<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

/** Volta para o login com uma mensagem de erro. */
function oauth_fail(string $message): void
{
    flash($message, 'error');
    redirect('admin/login.php');
}

// O provedor e o "state" ficam guardados na sessão desde o clique no botão.
$flow = $_SESSION['oauth'] ?? null;
unset($_SESSION['oauth']);

if (isset($_GET['error'])) {
    oauth_fail('Login cancelado ou negado pela rede social.');
}

$state = input('state');
$code  = input('code');

if (
    !is_array($flow)
    || $state === ''
    || !hash_equals((string) $flow['state'], $state)
    || time() - (int) $flow['time'] > 600
    || !isset(oauth_providers()[$flow['provider']])
) {
    oauth_fail('Sessão de login expirada ou inválida. Tente novamente.');
}
if ($code === '') {
    oauth_fail('A rede social não retornou o código de autorização.');
}

try {
    $profile = oauth_fetch_profile($flow['provider'], $code);
} catch (RuntimeException $ex) {
    error_log('[Bora+ OAuth] ' . $ex->getMessage());
    oauth_fail('Não foi possível concluir o login. Tente novamente.');
}

if ($profile['email'] === '' || !$profile['verified']) {
    oauth_fail('A conta não tem um e-mail verificado disponível.');
}

$adminId = oauth_resolve_admin($profile['email']);
if ($adminId === null) {
    oauth_fail('O e-mail ' . $profile['email'] . ' não tem permissão para acessar o admin.');
}

login_admin($adminId);
flash('Bem-vindo(a)' . ($profile['name'] !== '' ? ', ' . $profile['name'] : '') . '!');
redirect('admin/');
