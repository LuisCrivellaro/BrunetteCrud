<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$places = fetch_establishments();
if (!$places) {
    flash('Cadastre um local (bar, balada ou festa) antes de criar eventos.', 'warn');
    redirect('admin/local_form.php');
}

$id = (int) input('id', '0');
$ev = $id ? fetch_event($id) : null;
if ($id && !$ev) {
    flash('Evento não encontrado.', 'error');
    redirect('admin/eventos.php');
}

$data = $ev ?? [
    'establishment_id' => (int) input('local', '0'),
    'title' => '', 'description' => '', 'event_date' => '', 'start_time' => '',
    'price' => '', 'genre' => '', 'image' => null,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data['establishment_id'] = (int) input('establishment_id', '0');
    foreach (['title', 'description', 'event_date', 'start_time', 'price', 'genre'] as $f) {
        $data[$f] = input($f);
    }

    if (!fetch_establishment($data['establishment_id'])) {
        $errors[] = 'Escolha o local do evento.';
    }
    if ($data['title'] === '') {
        $errors[] = 'Informe o nome do evento.';
    }
    $dateOk = DateTime::createFromFormat('Y-m-d', $data['event_date']);
    if (!$dateOk || $dateOk->format('Y-m-d') !== $data['event_date']) {
        $errors[] = 'Informe uma data válida.';
    }
    if ($data['start_time'] !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $data['start_time'])) {
        $errors[] = 'Horário inválido.';
    }

    $oldImage = $ev['image'] ?? null;
    $image = handle_image_upload('image', $oldImage, input('remove_image') === '1', $errors);

    if (!$errors) {
        $values = [
            $data['establishment_id'], $data['title'], $data['description'], $data['event_date'],
            $data['start_time'] ?: null, $data['price'], $data['genre'], $image,
        ];
        if ($ev) {
            $values[] = $ev['id'];
            db()->prepare(
                'UPDATE events SET establishment_id=?, title=?, description=?, event_date=?, start_time=?,
                 price=?, genre=?, image=? WHERE id=?'
            )->execute($values);
            if ($oldImage !== $image) {
                delete_upload($oldImage);
            }
            flash('Evento atualizado.');
        } else {
            db()->prepare(
                'INSERT INTO events (establishment_id, title, description, event_date, start_time, price, genre, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute($values);
            flash('Evento criado e já está no ar!');
        }
        redirect('admin/eventos.php');
    }

    if ($image !== $oldImage) {
        delete_upload($image);
    }
    $data['image'] = $oldImage;
}

$pageTitle = $ev ? 'Editar evento' : 'Novo evento';
$active = 'eventos';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <a class="back" href="<?= e(url('admin/eventos.php')) ?>">← Eventos</a>
        <h1><?= $ev ? 'Editar evento' : 'Novo evento' ?></h1>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="form panel" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <label class="field">
        <span>Local *</span>
        <select name="establishment_id" required>
            <option value="">Selecione…</option>
            <?php foreach ($places as $es): ?>
                <option value="<?= (int) $es['id'] ?>" <?= (int) $data['establishment_id'] === (int) $es['id'] ? 'selected' : '' ?>>
                    <?= e($es['name']) ?> — <?= e(type_label($es['type'])) ?>, <?= e($es['neighborhood']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="field">
        <span>Nome do evento *</span>
        <input type="text" name="title" value="<?= e($data['title']) ?>" maxlength="140" required>
    </label>

    <div class="form__row form__row--3">
        <label class="field">
            <span>Data *</span>
            <input type="date" name="event_date" value="<?= e($data['event_date']) ?>" required>
        </label>
        <label class="field">
            <span>Horário</span>
            <input type="time" name="start_time" value="<?= e(substr((string) $data['start_time'], 0, 5)) ?>">
        </label>
        <label class="field">
            <span>Entrada</span>
            <input type="text" name="price" value="<?= e($data['price']) ?>" maxlength="40" placeholder="Grátis, R$ 40…">
        </label>
    </div>

    <label class="field">
        <span>Estilo musical / categoria</span>
        <input type="text" name="genre" value="<?= e($data['genre']) ?>" maxlength="60" placeholder="Eletrônica, Samba, Rock…">
    </label>

    <label class="field">
        <span>Descrição</span>
        <textarea name="description" rows="5" maxlength="1500"><?= e($data['description']) ?></textarea>
    </label>

    <div class="field">
        <span>Imagem do evento <small class="muted">(opcional — usa a do local se vazio)</small></span>
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
        <button class="btn" type="submit"><?= $ev ? 'Salvar alterações' : 'Criar evento' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/eventos.php')) ?>">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
