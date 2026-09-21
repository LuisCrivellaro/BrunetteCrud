<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

define('SITE_NAME', 'Bora+');
define('SITE_CITY', 'Curitiba');

define('BASE_PATH', dirname(__DIR__));
define('DB_FILE', BASE_PATH . '/database/bora.sqlite');
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('MAX_UPLOAD_BYTES', 4 * 1024 * 1024);

// Credenciais criadas na primeira execução. Troque a senha em Admin > Minha conta.
define('ADMIN_DEFAULT_EMAIL', 'admin@bora.com');
define('ADMIN_DEFAULT_PASSWORD', 'admin123');

const ESTABLISHMENT_TYPES = [
    'balada'       => 'Balada',
    'bar'          => 'Bar',
    'festa'        => 'Festa',
    'casa-de-show' => 'Casa de show',
    'pub'          => 'Pub',
];

const NEIGHBORHOOD_SUGGESTIONS = [
    'Centro', 'Batel', 'Água Verde', 'São Francisco', 'Santa Felicidade', 'Juvevê',
    'Bigorrilho', 'Alto da XV', 'Cabral', 'Portão', 'Rebouças', 'Centro Cívico',
    'Mercês', 'Cristo Rei', 'Ahú', 'Bom Retiro', 'Jardim Botânico', 'Seminário',
    'Vila Izabel', 'Hauer', 'Boqueirão', 'Santa Cândida',
];

/**
 * Descobre o caminho público do projeto (ex.: "/Bora%2B" no XAMPP, "" com php -S).
 */
function detect_base_url(): string
{
    $doc  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $root = realpath(BASE_PATH) ?: BASE_PATH;
    $doc  = rtrim(str_replace('\\', '/', $doc), '/');
    $root = str_replace('\\', '/', $root);

    if ($doc !== '' && str_starts_with(strtolower($root), strtolower($doc))) {
        $rel = substr($root, strlen($doc));
        if ($rel === '' || $rel === false) {
            return '';
        }
        return implode('/', array_map('rawurlencode', explode('/', $rel)));
    }
    return '';
}

define('BASE_URL', detect_base_url());
