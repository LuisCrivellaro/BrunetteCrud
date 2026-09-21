<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if (is_admin()) {
    redirect('admin/');
}

$provider  = input('provider');
$providers = oauth_providers();

if (!isset($providers[$provider])) {
    flash('Rede de login desconhecida.', 'error');
    redirect('admin/login.php');
}

if (!oauth_is_configured($provider)) {
    flash(
        'O login com ' . $providers[$provider]['label'] . ' ainda não foi configurado. '
        . 'Preencha as chaves em includes/oauth_config.php (URI de redirecionamento: '
        . oauth_redirect_uri() . ').',
        'warn'
    );
    redirect('admin/login.php');
}

$state = bin2hex(random_bytes(20));
$_SESSION['oauth'] = ['state' => $state, 'provider' => $provider, 'time' => time()];

header('Location: ' . oauth_authorize_url($provider, $state));
exit;
