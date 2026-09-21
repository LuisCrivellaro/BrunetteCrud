<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$q            = input('q');
$type         = input('tipo');
$neighborhood = input('bairro');
$when         = input('quando');

if (!isset(ESTABLISHMENT_TYPES[$type])) {
    $type = '';
}
if (!in_array($when, ['hoje', 'semana', 'mes'], true)) {
    $when = '';
}

$events = fetch_events([
    'upcoming'     => true,
    'q'            => $q,
    'type'         => $type,
    'neighborhood' => $neighborhood,
    'when'         => $when,
]);
$places = fetch_establishments(['q' => $q, 'type' => $type, 'neighborhood' => $neighborhood]);
$neighborhoods = fetch_neighborhoods();
$hasFilter = $q !== '' || $type !== '' || $neighborhood !== '' || $when !== '';

/** Monta a URL mantendo os filtros atuais, trocando apenas o informado. */
function filter_url(array $override): string
{
    $params = array_filter(array_merge([
        'q'      => input('q'),
        'tipo'   => input('tipo'),
        'bairro' => input('bairro'),
        'quando' => input('quando'),
    ], $override), static fn ($v) => $v !== '' && $v !== null);
    return url('index.php' . ($params ? '?' . http_build_query($params) : '') . '#eventos');
}

$pageTitle = SITE_NAME . ' — Baladas, bares e eventos em ' . SITE_CITY;
$active = 'eventos';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <p class="eyebrow">Curitiba · Noite</p>
        <h1>Bora pra onde<br>hoje à noite?</h1>
        <p class="lead">Baladas, bares e eventos da cidade num só lugar.</p>

        <form class="search" method="get" action="<?= e(url('index.php')) ?>#eventos" data-autosubmit>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar evento, bar ou balada…" aria-label="Buscar">
            <select name="bairro" aria-label="Bairro">
                <option value="">Todos os bairros</option>
                <?php foreach ($neighborhoods as $nb): ?>
                    <option value="<?= e($nb) ?>" <?= $nb === $neighborhood ? 'selected' : '' ?>><?= e($nb) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($type): ?><input type="hidden" name="tipo" value="<?= e($type) ?>"><?php endif; ?>
            <?php if ($when): ?><input type="hidden" name="quando" value="<?= e($when) ?>"><?php endif; ?>
            <button type="submit" class="btn">Buscar</button>
        </form>
    </div>
</section>

<section class="section" id="eventos">
    <div class="container">
        <div class="section__head">
            <h2>Próximos eventos</h2>
            <?php if ($hasFilter): ?>
                <a class="clear" href="<?= e(url('index.php#eventos')) ?>">Limpar filtros ✕</a>
            <?php endif; ?>
        </div>

        <div class="chips">
            <a class="chip <?= $when === '' ? 'is-active' : '' ?>" href="<?= e(filter_url(['quando' => ''])) ?>">Todos</a>
            <a class="chip <?= $when === 'hoje' ? 'is-active' : '' ?>" href="<?= e(filter_url(['quando' => 'hoje'])) ?>">Hoje</a>
            <a class="chip <?= $when === 'semana' ? 'is-active' : '' ?>" href="<?= e(filter_url(['quando' => 'semana'])) ?>">Esta semana</a>
            <a class="chip <?= $when === 'mes' ? 'is-active' : '' ?>" href="<?= e(filter_url(['quando' => 'mes'])) ?>">Este mês</a>
            <span class="chips__sep"></span>
            <a class="chip <?= $type === '' ? 'is-active' : '' ?>" href="<?= e(filter_url(['tipo' => ''])) ?>">Tudo</a>
            <?php foreach (ESTABLISHMENT_TYPES as $key => $label): ?>
                <a class="chip <?= $type === $key ? 'is-active' : '' ?>" href="<?= e(filter_url(['tipo' => $key])) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($events): ?>
            <div class="grid">
                <?php foreach ($events as $ev) { render_event_card($ev); } ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <p>Nenhum evento encontrado.</p>
                <span class="muted">Tente outros filtros ou volte em breve.</span>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--alt" id="lugares">
    <div class="container">
        <div class="section__head">
            <h2>Lugares</h2>
            <span class="muted"><?= count($places) ?> <?= count($places) === 1 ? 'resultado' : 'resultados' ?></span>
        </div>

        <?php if ($places): ?>
            <div class="grid">
                <?php foreach ($places as $es) { render_place_card($es); } ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <p>Nenhum lugar encontrado.</p>
                <span class="muted">Tente outros filtros.</span>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
