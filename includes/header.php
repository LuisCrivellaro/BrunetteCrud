<?php
/** @var string $pageTitle */
$pageTitle = $pageTitle ?? SITE_NAME . ' — Baladas, bares e eventos em ' . SITE_CITY;
$pageDesc  = $pageDesc ?? 'Descubra baladas, bares e eventos em ' . SITE_CITY . '.';
$active    = $active ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="logo" href="<?= e(url('index.php')) ?>">Bora<span>+</span></a>
        <nav class="nav">
            <a href="<?= e(url('index.php#eventos')) ?>" class="<?= $active === 'eventos' ? 'is-active' : '' ?>">Eventos</a>
            <a href="<?= e(url('index.php#lugares')) ?>" class="<?= $active === 'lugares' ? 'is-active' : '' ?>">Lugares</a>
            <a href="<?= e(url('admin/login.php')) ?>" class="nav__admin">Login</a>
        </nav>
    </div>
</header>
<main>
