<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if (is_admin()) {
    redirect('admin/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (attempt_login(input('email'), (string) ($_POST['password'] ?? ''))) {
        redirect('admin/');
    }
    $error = 'E-mail ou senha incorretos.';
}

// Mensagens vindas do login social (erros, avisos de configuração)
$flash = pull_flash();
$providers = oauth_providers();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Entrar — <?= e(SITE_NAME) ?> Admin</title>
    <link rel="icon" type="image/png" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin login-page">
    <div class="login">
        <a class="logo" href="<?= e(url('index.php')) ?>">Bora<span>+</span></a>
        <h1>Área do admin</h1>
        <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($flash): ?><div class="alert alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

        <form class="login__form" method="post" novalidate>
            <?= csrf_field() ?>
            <label class="field">
                <span>E-mail</span>
                <input type="email" name="email" value="<?= e(input('email')) ?>" required autofocus autocomplete="username">
            </label>
            <label class="field">
                <span>Senha</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn--block" type="submit">Entrar</button>
        </form>

        <div class="divider"><span>ou continue com</span></div>

        <div class="social">
            <?php foreach ($providers as $key => $p): ?>
                <a class="social__btn <?= oauth_is_configured($key) ? '' : 'is-off' ?>"
                   href="<?= e(url('admin/oauth_start.php?provider=' . $key)) ?>">
                    <?= oauth_icon($key) ?>
                    <span><?= e($p['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <a class="back" href="<?= e(url('index.php')) ?>">← Voltar ao site</a>
    </div>
</body>
</html>
