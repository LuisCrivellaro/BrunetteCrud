<?php
declare(strict_types=1);

/** Imagem (ou degradê) usada como capa de um card. */
function cover(?string $image, string $seed, string $label, string $class = 'cover'): string
{
    $src = upload_url($image);
    if ($src) {
        return '<div class="' . $class . '"><img src="' . e($src) . '" alt="" loading="lazy"></div>';
    }
    return '<div class="' . $class . ' cover--ph" style="' . e(placeholder_style($seed)) . '"><span>'
        . e(initials($label)) . '</span></div>';
}

function render_event_card(array $ev): void
{
    $d = date_parts($ev['event_date']);
    $image = $ev['image'] ?: $ev['place_image'];
    $link = url('evento.php?id=' . (int) $ev['id']);
    $meta = array_filter([format_time($ev['start_time']), $ev['price']]);
    ?>
    <a class="card reveal" href="<?= e($link) ?>">
        <?= cover($image, $ev['place_name'], $ev['place_name']) ?>
        <div class="date-badge">
            <strong><?= e($d['day']) ?></strong>
            <span><?= e($d['month']) ?></span>
        </div>
        <div class="card__body">
            <span class="tag"><?= e(type_label($ev['place_type'])) ?><?= $ev['genre'] ? ' · ' . e($ev['genre']) : '' ?></span>
            <h3><?= e($ev['title']) ?></h3>
            <p class="muted"><?= e($ev['place_name']) ?> · <?= e($ev['place_neighborhood']) ?></p>
            <?php if ($meta): ?>
                <p class="card__meta"><?= e(implode(' · ', $meta)) ?></p>
            <?php endif; ?>
        </div>
    </a>
    <?php
}

function render_place_card(array $es): void
{
    $link = url('local.php?id=' . (int) $es['id']);
    $n = (int) ($es['upcoming_count'] ?? 0);
    ?>
    <a class="card reveal" href="<?= e($link) ?>">
        <?= cover($es['image'], $es['name'], $es['name']) ?>
        <div class="card__body">
            <span class="tag"><?= e(type_label($es['type'])) ?></span>
            <h3><?= e($es['name']) ?></h3>
            <p class="muted"><?= e($es['neighborhood']) ?></p>
            <p class="card__meta"><?= $n > 0 ? $n . ($n === 1 ? ' evento em breve' : ' eventos em breve') : 'Sem eventos agendados' ?></p>
        </div>
    </a>
    <?php
}
