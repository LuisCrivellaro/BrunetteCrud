<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$admin = current_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email   = input('email');
    $current = (string) ($_POST['current_password'] ?? '');
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }
    if (!password_verify($current, $admin['password_hash'])) {
        $errors[] = 'A senha atual está incorreta.';
    }
    if ($new !== '') {
        if (strlen($new) < 8) {
            $errors[] = 'A nova senha deve ter pelo menos 8 caracteres.';
        }
        if ($new !== $confirm) {
            $errors[] = 'A confirmação não confere com a nova senha.';
        }
    }

    if (!$errors) {
        $hash = $new !== '' ? password_hash($new, PASSWORD_DEFAULT) : $admin['password_hash'];
        try {
            db()->prepare('UPDATE admins SET email = ?, password_hash = ? WHERE id = ?')
                ->execute([$email, $hash, $admin['id']]);
            flash('Conta atualizada.');
            redirect('admin/conta.php');
        } catch (PDOException $ex) {
            $errors[] = 'Este e-mail já está em uso.';
        }
    }
    $admin['email'] = $email;
}

$pageTitle = 'Minha conta';
$active = 'conta';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <h1>Minha conta</h1>
        <p class="muted">Altere seu e-mail de acesso e sua senha.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="form panel form--narrow" method="post">
    <?= csrf_field() ?>
    <label class="field">
        <span>E-mail</span>
        <input type="email" name="email" value="<?= e($admin['email']) ?>" required autocomplete="username">
    </label>
    <label class="field">
        <span>Senha atual *</span>
        <input type="password" name="current_password" required autocomplete="current-password">
    </label>
    <label class="field">
        <span>Nova senha <small class="muted">(deixe em branco para manter)</small></span>
        <input type="password" name="new_password" minlength="8" autocomplete="new-password">
    </label>
    <label class="field">
        <span>Confirmar nova senha</span>
        <input type="password" name="confirm_password" autocomplete="new-password">
    </label>
    <div class="form__actions">
        <button class="btn" type="submit">Salvar</button>
    </div>
</form>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
