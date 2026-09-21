<?php
/** @var string $pageTitle */
$pageTitle = ($pageTitle ?? 'Painel') . ' — ' . SITE_NAME . ' Admin';
$active    = $active ?? '';
$flash     = pull_flash();
$admin     = current_admin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<header class="site-header">
    <div class="container site-header__inner">
        <a class="logo" href="<?= e(url('admin/')) ?>">Bora<span>+</span> <small>admin</small></a>
        <nav class="nav">
            <a href="<?= e(url('admin/')) ?>" class="<?= $active === 'painel' ? 'is-active' : '' ?>">Painel</a>
            <a href="<?= e(url('admin/locais.php')) ?>" class="<?= $active === 'locais' ? 'is-active' : '' ?>">Locais</a>
            <a href="<?= e(url('admin/eventos.php')) ?>" class="<?= $active === 'eventos' ? 'is-active' : '' ?>">Eventos</a>
            <a href="<?= e(url('admin/conta.php')) ?>" class="<?= $active === 'conta' ? 'is-active' : '' ?>">Conta</a>
            <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">Ver site ↗</a>
            <form method="post" action="<?= e(url('admin/logout.php')) ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="link-btn">Sair</button>
            </form>
        </nav>
    </div>
</header>
<main class="container admin-main">
<?php if ($flash): ?>
    <div class="alert alert--<?= e($flash['type']) ?>" data-autodismiss><?= e($flash['message']) ?></div>
<?php endif; ?>
