<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$es = fetch_establishment((int) input('id', '0'));
if (!$es) {
    http_response_code(404);
    $pageTitle = 'Local não encontrado — ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty"><p>Local não encontrado.</p>'
        . '<a class="btn btn--ghost" href="' . e(url('index.php')) . '">Voltar</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$events = fetch_events(['upcoming' => true, 'establishment_id' => $es['id']]);
$insta = instagram_url($es['instagram']);

$pageTitle = $es['name'] . ' — ' . type_label($es['type']) . ' em ' . SITE_CITY . ' · ' . SITE_NAME;
$pageDesc = $es['description'] ?: ($es['name'] . ' — ' . $es['neighborhood']);
$active = 'lugares';
require __DIR__ . '/includes/header.php';
?>

<section class="detail">
    <div class="container detail__grid">
        <div class="detail__media">
            <?= cover($es['image'], $es['name'], $es['name'], 'cover cover--lg') ?>
        </div>

        <div class="detail__info">
            <a class="back" href="<?= e(url('index.php#lugares')) ?>">← Lugares</a>
            <span class="tag"><?= e(type_label($es['type'])) ?></span>
            <h1><?= e($es['name']) ?></h1>

            <dl class="facts">
                <div>
                    <dt>Bairro</dt>
                    <dd><?= e($es['neighborhood']) ?></dd>
                </div>
                <?php if ($es['address']): ?>
                    <div><dt>Endereço</dt><dd><?= e($es['address']) ?></dd></div>
                <?php endif; ?>
                <?php if ($es['opening_hours']): ?>
                    <div><dt>Horário</dt><dd><?= e($es['opening_hours']) ?></dd></div>
                <?php endif; ?>
            </dl>

            <?php if ($es['description']): ?>
                <p class="prose"><?= nl2br(e($es['description'])) ?></p>
            <?php endif; ?>

            <div class="actions">
                <a class="btn" href="<?= e(maps_url($es['address'], $es['name'])) ?>" target="_blank" rel="noopener">Como chegar</a>
                <?php if ($insta): ?>
                    <a class="btn btn--ghost" href="<?= e($insta) ?>" target="_blank" rel="noopener">Instagram</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__head"><h2>Próximos eventos</h2></div>
        <?php if ($events): ?>
            <div class="grid">
                <?php foreach ($events as $ev) { render_event_card($ev); } ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <p>Sem eventos agendados.</p>
                <span class="muted">Volte em breve para conferir a programação.</span>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
