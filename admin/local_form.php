<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$id = (int) input('id', '0');
$es = $id ? fetch_establishment($id) : null;
if ($id && !$es) {
    flash('Local não encontrado.', 'error');
    redirect('admin/locais.php');
}

$data = $es ?? [
    'name' => '', 'type' => 'bar', 'neighborhood' => '', 'address' => '',
    'opening_hours' => '', 'instagram' => '', 'description' => '', 'image' => null,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    foreach (['name', 'type', 'neighborhood', 'address', 'opening_hours', 'instagram', 'description'] as $f) {
        $data[$f] = input($f);
    }

    if ($data['name'] === '') {
        $errors[] = 'Informe o nome do bar, balada ou festa.';
    }
    if (!isset(ESTABLISHMENT_TYPES[$data['type']])) {
        $errors[] = 'Escolha um tipo válido.';
    }
    if ($data['neighborhood'] === '') {
        $errors[] = 'Informe o bairro.';
    }

    $oldImage = $es['image'] ?? null;
    $image = handle_image_upload('image', $oldImage, input('remove_image') === '1', $errors);

    if (!$errors) {
        $values = [
            $data['name'], $data['type'], $data['neighborhood'], $data['address'],
            $data['opening_hours'], $data['instagram'], $data['description'], $image,
        ];
        if ($es) {
            $values[] = $es['id'];
            db()->prepare(
                'UPDATE establishments SET name=?, type=?, neighborhood=?, address=?, opening_hours=?,
                 instagram=?, description=?, image=? WHERE id=?'
            )->execute($values);
            if ($oldImage !== $image) {
                delete_upload($oldImage);
            }
            flash('Local atualizado.');
            redirect('admin/locais.php');
        }

        db()->prepare(
            'INSERT INTO establishments (name, type, neighborhood, address, opening_hours, instagram, description, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute($values);
        $newId = (int) db()->lastInsertId();
        flash('Local criado! Agora você pode adicionar eventos a ele.');
        redirect('admin/evento_form.php?local=' . $newId);
    }
    // Houve erro: descarta o arquivo recém-enviado e mantém a imagem atual.
    if ($image !== $oldImage) {
        delete_upload($image);
    }
    $data['image'] = $oldImage;
}

$suggestions = array_unique(array_merge(NEIGHBORHOOD_SUGGESTIONS, fetch_neighborhoods()));
sort($suggestions, SORT_LOCALE_STRING);

$pageTitle = $es ? 'Editar local' : 'Novo local';
$active = 'locais';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <a class="back" href="<?= e(url('admin/locais.php')) ?>">← Locais</a>
        <h1><?= $es ? 'Editar local' : 'Novo local' ?></h1>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="form panel" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <label class="field">
        <span>Nome do bar, balada ou festa *</span>
        <input type="text" name="name" value="<?= e($data['name']) ?>" maxlength="120" required autofocus>
    </label>

    <div class="form__row">
        <label class="field">
            <span>Tipo *</span>
            <select name="type" required>
                <?php foreach (ESTABLISHMENT_TYPES as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $data['type'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span>Bairro *</span>
            <input type="text" name="neighborhood" value="<?= e($data['neighborhood']) ?>" list="bairros" maxlength="80" required>
            <datalist id="bairros">
                <?php foreach ($suggestions as $nb): ?><option value="<?= e($nb) ?>"><?php endforeach; ?>
            </datalist>
        </label>
    </div>

    <label class="field">
        <span>Endereço</span>
        <input type="text" name="address" value="<?= e($data['address']) ?>" maxlength="160" placeholder="Rua, número">
    </label>

    <div class="form__row">
        <label class="field">
            <span>Horário de funcionamento</span>
            <input type="text" name="opening_hours" value="<?= e($data['opening_hours']) ?>" maxlength="120" placeholder="Qui a sáb, 22h às 5h">
        </label>
        <label class="field">
            <span>Instagram</span>
            <input type="text" name="instagram" value="<?= e($data['instagram']) ?>" maxlength="60" placeholder="@seubar">
        </label>
    </div>

    <label class="field">
        <span>Descrição</span>
        <textarea name="description" rows="5" maxlength="1500"><?= e($data['description']) ?></textarea>
    </label>

    <div class="field">
        <span>Imagem de capa</span>
        <div class="upload">
            <?php if (!empty($data['image'])): ?>
                <img src="<?= e(upload_url($data['image'])) ?>" alt="" class="upload__preview" data-preview>
            <?php else: ?>
                <img src="" alt="" class="upload__preview" data-preview hidden>
            <?php endif; ?>
            <div>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview-input>
                <small class="muted">JPG, PNG ou WebP · até 4 MB</small>
                <?php if (!empty($data['image'])): ?>
                    <label class="check"><input type="checkbox" name="remove_image" value="1"> Remover imagem atual</label>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form__actions">
        <button class="btn" type="submit"><?= $es ? 'Salvar alterações' : 'Criar local' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/locais.php')) ?>">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
