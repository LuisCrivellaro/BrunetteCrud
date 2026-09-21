<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && input('action') === 'delete') {
    csrf_verify();
    $es = fetch_establishment((int) input('id', '0'));
    if ($es) {
        // Apaga também as imagens dos eventos, que saem junto por cascata.
        $stmt = db()->prepare('SELECT image FROM events WHERE establishment_id = ?');
        $stmt->execute([$es['id']]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $img) {
            delete_upload($img);
        }
        delete_upload($es['image']);
        db()->prepare('DELETE FROM establishments WHERE id = ?')->execute([$es['id']]);
        flash('Local "' . $es['name'] . '" removido.');
    }
    redirect('admin/locais.php');
}

$places = fetch_establishments();

$pageTitle = 'Locais';
$active = 'locais';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <h1>Locais</h1>
        <p class="muted">Bares, baladas, festas e casas de show.</p>
    </div>
    <a class="btn" href="<?= e(url('admin/local_form.php')) ?>">+ Novo local</a>
</div>

<div class="panel">
    <?php if ($places): ?>
        <ul class="rows">
            <?php foreach ($places as $es): ?>
                <li>
                    <div class="row-main">
                        <?= cover($es['image'], $es['name'], $es['name'], 'thumb') ?>
                        <div>
                            <strong><?= e($es['name']) ?></strong>
                            <span class="muted"><?= e(type_label($es['type'])) ?> · <?= e($es['neighborhood']) ?></span>
                        </div>
                    </div>
                    <span class="muted"><?= (int) $es['upcoming_count'] ?> próx. eventos</span>
                    <div class="row-actions">
                        <a class="btn btn--sm" href="<?= e(url('admin/evento_form.php?local=' . (int) $es['id'])) ?>">+ Evento</a>
                        <a class="btn btn--sm btn--ghost" href="<?= e(url('admin/local_form.php?id=' . (int) $es['id'])) ?>">Editar</a>
                        <form method="post" class="inline" data-confirm="Remover &quot;<?= e($es['name']) ?>&quot; e todos os seus eventos?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $es['id'] ?>">
                            <button class="btn btn--sm btn--danger" type="submit">Excluir</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted pad">Nenhum local cadastrado ainda.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
