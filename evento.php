<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$ev = fetch_event((int) input('id', '0'));
if (!$ev) {
    http_response_code(404);
    $pageTitle = 'Evento não encontrado — ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty"><p>Evento não encontrado.</p>'
        . '<a class="btn btn--ghost" href="' . e(url('index.php')) . '">Voltar</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$others = array_filter(
    fetch_events(['upcoming' => true, 'establishment_id' => $ev['establishment_id']]),
    static fn ($o) => (int) $o['id'] !== (int) $ev['id']
);

$d = date_parts($ev['event_date']);
$image = $ev['image'] ?: $ev['place_image'];
$pageTitle = $ev['title'] . ' — ' . $ev['place_name'] . ' · ' . SITE_NAME;
$pageDesc = $ev['description'] ?: ($ev['title'] . ' em ' . $ev['place_name']);
$isPast = $ev['event_date'] < today();
require __DIR__ . '/includes/header.php';
?>

<section class="detail">
    <div class="container detail__grid">
        <div class="detail__media">
            <?= cover($image, $ev['place_name'], $ev['place_name'], 'cover cover--lg') ?>
        </div>

        <div class="detail__info">
            <a class="back" href="<?= e(url('index.php#eventos')) ?>">← Eventos</a>
            <span class="tag"><?= e(type_label($ev['place_type'])) ?><?= $ev['genre'] ? ' · ' . e($ev['genre']) : '' ?></span>
            <h1><?= e($ev['title']) ?></h1>
            <?php if ($isPast): ?><p class="notice">Este evento já aconteceu.</p><?php endif; ?>

            <dl class="facts">
                <div>
                    <dt>Quando</dt>
                    <dd><?= e(format_date($ev['event_date'])) ?><?= $ev['start_time'] ? ' · ' . e(format_time($ev['start_time'])) : '' ?></dd>
                </div>
                <div>
                    <dt>Onde</dt>
                    <dd>
                        <a href="<?= e(url('local.php?id=' . (int) $ev['establishment_id'])) ?>"><?= e($ev['place_name']) ?></a><br>
                        <span class="muted"><?= e($ev['place_address'] ?: $ev['place_neighborhood']) ?></span>
                    </dd>
                </div>
                <?php if ($ev['price']): ?>
                    <div>
                        <dt>Entrada</dt>
                        <dd><?= e($ev['price']) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>

            <?php if ($ev['description']): ?>
                <p class="prose"><?= nl2br(e($ev['description'])) ?></p>
            <?php endif; ?>

            <div class="actions">
                <a class="btn" href="<?= e(maps_url($ev['place_address'], $ev['place_name'])) ?>" target="_blank" rel="noopener">Como chegar</a>
                <a class="btn btn--ghost" href="<?= e(url('local.php?id=' . (int) $ev['establishment_id'])) ?>">Ver o local</a>
            </div>
        </div>
    </div>
</section>

<?php if ($others): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__head"><h2>Mais em <?= e($ev['place_name']) ?></h2></div>
        <div class="grid">
            <?php foreach ($others as $o) { render_event_card($o); } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
