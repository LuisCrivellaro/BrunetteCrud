<?php
declare(strict_types=1);

const WEEKDAYS = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
const MONTHS   = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];

/* ---------- Saída e URLs ---------- */

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function upload_url(?string $file): ?string
{
    return $file ? url('uploads/' . rawurlencode($file)) : null;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/* ---------- Mensagens flash ---------- */

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function pull_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Datas e textos ---------- */

function today(): string
{
    return date('Y-m-d');
}

function date_parts(string $date): array
{
    $t = strtotime($date);
    return [
        'day'     => date('d', $t),
        'month'   => MONTHS[(int) date('n', $t) - 1],
        'weekday' => WEEKDAYS[(int) date('w', $t)],
        'year'    => date('Y', $t),
    ];
}

function format_date(string $date): string
{
    $p = date_parts($date);
    $label = ucfirst($p['weekday']) . ', ' . $p['day'] . ' de ' . $p['month'];
    return $p['year'] !== date('Y') ? $label . ' ' . $p['year'] : $label;
}

function format_time(?string $time): string
{
    return $time ? str_replace(':', 'h', substr($time, 0, 5)) : '';
}

function type_label(string $type): string
{
    return ESTABLISHMENT_TYPES[$type] ?? ucfirst($type);
}

function initials(string $name): string
{
    $words = preg_split('/\s+/u', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $out .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    return $out;
}

/** Degradê estável gerado a partir do nome, usado quando não há imagem. */
function placeholder_style(string $seed): string
{
    $h1 = crc32($seed) % 360;
    $h2 = ($h1 + 45) % 360;
    return "background:linear-gradient(135deg,hsl($h1 65% 60%),hsl($h2 70% 52%))";
}

function maps_url(?string $address, string $name = ''): string
{
    $q = trim(($address ?: $name) . ', ' . SITE_CITY . ' - PR');
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($q);
}

function instagram_url(?string $handle): ?string
{
    $h = ltrim(trim((string) $handle), '@');
    return $h !== '' ? 'https://instagram.com/' . rawurlencode($h) : null;
}

/* ---------- Upload de imagens ---------- */

function delete_upload(?string $file): void
{
    if (!$file) {
        return;
    }
    $path = UPLOAD_DIR . '/' . basename($file);
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Processa um campo de upload de imagem.
 * Retorna o nome do arquivo a gravar no banco (novo, atual ou null).
 * Não apaga o arquivo antigo: quem chama faz isso depois de salvar no banco.
 */
function handle_image_upload(string $field, ?string $current, bool $remove, array &$errors): ?string
{
    if ($remove) {
        $current = null;
    }

    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Não foi possível enviar a imagem.';
        return $current;
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        $errors[] = 'A imagem deve ter no máximo 4 MB.';
        return $current;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        $errors[] = 'Formato de imagem inválido. Use JPG, PNG ou WebP.';
        return $current;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        $errors[] = 'Falha ao salvar a imagem no servidor.';
        return $current;
    }

    return $name;
}

/* ---------- Entrada ---------- */

function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function like_escape(string $s): string
{
    return '%' . addcslashes($s, '%_\\') . '%';
}
