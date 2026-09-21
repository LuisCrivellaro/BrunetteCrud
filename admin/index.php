<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$totalPlaces = (int) db()->query('SELECT COUNT(*) FROM establishments')->fetchColumn();
$totalEvents = (int) db()->query('SELECT COUNT(*) FROM events')->fetchColumn();
$stmt = db()->prepare('SELECT COUNT(*) FROM events WHERE event_date >= ?');
$stmt->execute([today()]);
$upcoming = (int) $stmt->fetchColumn();

$next = fetch_events(['upcoming' => true], 6);
$admin = current_admin();
$defaultPass = $admin && password_verify(ADMIN_DEFAULT_PASSWORD, $admin['password_hash']);

$pageTitle = 'Painel';
$active = 'painel';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <h1>Painel</h1>
        <p class="muted">Gerencie os locais e eventos do site.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('admin/local_form.php')) ?>">+ Novo local</a>
        <a class="btn" href="<?= e(url('admin/evento_form.php')) ?>">+ Novo evento</a>
    </div>
</div>

<?php if ($defaultPass): ?>
    <div class="alert alert--warn">
        Você ainda usa a senha padrão. <a href="<?= e(url('admin/conta.php')) ?>">Altere agora</a> por segurança.
    </div>
<?php endif; ?>

<div class="stats">
    <a class="stat" href="<?= e(url('admin/locais.php')) ?>"><strong><?= $totalPlaces ?></strong><span>Locais</span></a>
    <a class="stat" href="<?= e(url('admin/eventos.php')) ?>"><strong><?= $totalEvents ?></strong><span>Eventos</span></a>
    <a class="stat" href="<?= e(url('admin/eventos.php')) ?>"><strong><?= $upcoming ?></strong><span>Próximos</span></a>
</div>

<div class="panel">
    <div class="panel__head"><h2>Próximos eventos</h2><a href="<?= e(url('admin/eventos.php')) ?>">Ver todos</a></div>
    <?php if ($next): ?>
        <ul class="rows">
            <?php foreach ($next as $ev): ?>
                <li>
                    <div>
                        <strong><?= e($ev['title']) ?></strong>
                        <span class="muted"><?= e($ev['place_name']) ?></span>
                    </div>
                    <span class="muted"><?= e(format_date($ev['event_date'])) ?></span>
                    <a class="btn btn--sm btn--ghost" href="<?= e(url('admin/evento_form.php?id=' . (int) $ev['id'])) ?>">Editar</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted pad">Nenhum evento futuro. Crie o primeiro!</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
