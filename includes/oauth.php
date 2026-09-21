<?php
declare(strict_types=1);

/**
 * Login social via OAuth 2.0 (fluxo "authorization code").
 * Redes: Google e Facebook.
 */

function oauth_settings(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $file = __DIR__ . '/oauth_config.php';
        $cfg = is_file($file) ? (array) require $file : [];
    }
    return $cfg;
}

function oauth_providers(): array
{
    return [
        'google' => [
            'label'     => 'Google',
            'auth_url'  => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'scope'     => 'openid email profile',
            'extra'     => ['prompt' => 'select_account'],
        ],
        'facebook' => [
            'label'     => 'Facebook',
            'auth_url'  => 'https://www.facebook.com/v19.0/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
            'scope'     => 'email',
            'extra'     => [],
        ],
    ];
}

function oauth_credentials(string $provider): array
{
    $c = oauth_settings()['providers'][$provider] ?? [];
    return [trim((string) ($c['client_id'] ?? '')), trim((string) ($c['client_secret'] ?? ''))];
}

function oauth_is_configured(string $provider): bool
{
    [$id, $secret] = oauth_credentials($provider);
    return $id !== '' && $secret !== '';
}

/** URI que deve ser cadastrada no painel de cada rede. */
function oauth_redirect_uri(): string
{
    $base = rtrim(trim((string) (oauth_settings()['base_url'] ?? '')), '/');
    if ($base === '') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL;
    }
    return $base . '/admin/oauth_callback.php';
}

function oauth_authorize_url(string $provider, string $state): string
{
    $p = oauth_providers()[$provider];
    [$clientId] = oauth_credentials($provider);

    return $p['auth_url'] . '?' . http_build_query([
        'client_id'     => $clientId,
        'redirect_uri'  => oauth_redirect_uri(),
        'response_type' => 'code',
        'scope'         => $p['scope'],
        'state'         => $state,
    ] + $p['extra']);
}

/** Requisição HTTP simples que devolve JSON decodificado ou lança RuntimeException. */
function oauth_http(string $url, ?array $postFields = null, array $headers = []): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensão cURL do PHP precisa estar ativa para o login social.');
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json', 'User-Agent: Bora+'], $headers),
    ]);
    if ($postFields !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    }

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Falha de conexão: ' . $err);
    }
    $json = json_decode((string) $raw, true);
    if ($status >= 400 || !is_array($json)) {
        throw new RuntimeException('Resposta inválida (HTTP ' . $status . '): ' . substr((string) $raw, 0, 200));
    }
    return $json;
}

/**
 * Troca o "code" por um token e busca o e-mail do usuário.
 * Retorna ['email' => string, 'verified' => bool, 'name' => string].
 */
function oauth_fetch_profile(string $provider, string $code): array
{
    $p = oauth_providers()[$provider];
    [$clientId, $secret] = oauth_credentials($provider);

    $token = oauth_http($p['token_url'], [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => oauth_redirect_uri(),
        'client_id'     => $clientId,
        'client_secret' => $secret,
    ]);
    $access = (string) ($token['access_token'] ?? '');
    if ($access === '') {
        throw new RuntimeException('Token de acesso não recebido.');
    }
    $bearer = ['Authorization: Bearer ' . $access];

    switch ($provider) {
        case 'google':
            $u = oauth_http('https://openidconnect.googleapis.com/v1/userinfo', null, $bearer);
            return [
                'email'    => (string) ($u['email'] ?? ''),
                'verified' => ($u['email_verified'] ?? false) === true,
                'name'     => (string) ($u['name'] ?? ''),
            ];

        case 'facebook':
            $u = oauth_http('https://graph.facebook.com/v19.0/me?' . http_build_query([
                'fields' => 'id,name,email',
            ]), null, $bearer);
            return [
                'email'    => (string) ($u['email'] ?? ''),
                'verified' => !empty($u['email']), // o Facebook só devolve e-mails confirmados
                'name'     => (string) ($u['name'] ?? ''),
            ];
    }

    throw new RuntimeException('Rede desconhecida.');
}

/**
 * Só e-mails listados em "allowed_emails" podem entrar. Se ainda não houver
 * administrador com esse e-mail, ele é criado (com senha aleatória).
 */
function oauth_resolve_admin(string $email): ?int
{
    $email = strtolower(trim($email));
    $allowed = array_map(
        static fn ($e) => strtolower(trim((string) $e)),
        (array) (oauth_settings()['allowed_emails'] ?? [])
    );
    if ($email === '' || !in_array($email, $allowed, true)) {
        return null;
    }

    $stmt = db()->prepare('SELECT id FROM admins WHERE lower(email) = ?');
    $stmt->execute([$email]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int) $id;
    }

    db()->prepare('INSERT INTO admins (email, password_hash) VALUES (?, ?)')
        ->execute([$email, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT)]);
    return (int) db()->lastInsertId();
}

/** Ícone SVG de cada rede (para os botões da tela de login). */
function oauth_icon(string $provider): string
{
    $icons = [
        'google' => '<svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.34l-.53 3.49h-2.8V24C19.62 23.1 24 18.1 24 12.07z"/></svg>',
    ];
    return $icons[$provider] ?? '';
}
