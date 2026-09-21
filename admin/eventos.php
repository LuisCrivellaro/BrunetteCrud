<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && input('action') === 'delete') {
    csrf_verify();
    $stmt = db()->prepare('SELECT id, title, image FROM events WHERE id = ?');
    $stmt->execute([(int) input('id', '0')]);
    $ev = $stmt->fetch();
    if ($ev) {
        delete_upload($ev['image']);
        db()->prepare('DELETE FROM events WHERE id = ?')->execute([$ev['id']]);
        flash('Evento "' . $ev['title'] . '" removido.');
    }
    redirect('admin/eventos.php');
}

$show = input('ver') === 'passados' ? 'passados' : 'proximos';
$events = fetch_events(
    $show === 'proximos' ? ['upcoming' => true] : ['order' => 'desc']
);
if ($show === 'passados') {
    $events = array_values(array_filter($events, static fn ($e) => $e['event_date'] < today()));
}

$pageTitle = 'Eventos';
$active = 'eventos';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <h1>Eventos</h1>
        <p class="muted">Adicione e edite a programação dos locais.</p>
    </div>
    <a class="btn" href="<?= e(url('admin/evento_form.php')) ?>">+ Novo evento</a>
</div>

<div class="chips">
    <a class="chip <?= $show === 'proximos' ? 'is-active' : '' ?>" href="<?= e(url('admin/eventos.php')) ?>">Próximos</a>
    <a class="chip <?= $show === 'passados' ? 'is-active' : '' ?>" href="<?= e(url('admin/eventos.php?ver=passados')) ?>">Passados</a>
</div>

<div class="panel">
    <?php if ($events): ?>
        <ul class="rows">
            <?php foreach ($events as $ev): $d = date_parts($ev['event_date']); ?>
                <li>
                    <div class="row-main">
                        <div class="mini-date"><strong><?= e($d['day']) ?></strong><span><?= e($d['month']) ?></span></div>
                        <div>
                            <strong><?= e($ev['title']) ?></strong>
                            <span class="muted"><?= e($ev['place_name']) ?><?= $ev['start_time'] ? ' · ' . e(format_time($ev['start_time'])) : '' ?></span>
                        </div>
                    </div>
                    <span class="muted"><?= e($ev['price'] ?: '—') ?></span>
                    <div class="row-actions">
                        <a class="btn btn--sm btn--ghost" href="<?= e(url('evento.php?id=' . (int) $ev['id'])) ?>" target="_blank" rel="noopener">Ver</a>
                        <a class="btn btn--sm btn--ghost" href="<?= e(url('admin/evento_form.php?id=' . (int) $ev['id'])) ?>">Editar</a>
                        <form method="post" class="inline" data-confirm="Remover o evento &quot;<?= e($ev['title']) ?>&quot;?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $ev['id'] ?>">
                            <button class="btn btn--sm btn--danger" type="submit">Excluir</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted pad">Nenhum evento <?= $show === 'proximos' ? 'agendado' : 'passado' ?>.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
