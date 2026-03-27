<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador']);
require_permission('usuarios.editar');

$userId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($userId <= 0) {
    set_flash('error', 'Usuario no válido.');
    redirect('usuarios.php');
}

$roles = $pdo->query('SELECT id, nombre FROM roles ORDER BY nombre')->fetchAll();
$roleIds = array_map(static fn(array $role): int => (int)$role['id'], $roles);

$stmt = $pdo->prepare('SELECT id, nombre, email, rol_id, bloqueado FROM usuarios WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'Usuario no encontrado.');
    redirect('usuarios.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
        redirect('editar_usuario.php?id=' . $userId);
    }

    $nombre = post('nombre');
    $email = post('email');
    $rolId = (int)($_POST['rol_id'] ?? 0);
    $newPassword = post('password');

    if ($nombre === '' || $email === '' || $rolId <= 0) {
        set_flash('error', 'Completa todos los campos requeridos.');
        redirect('editar_usuario.php?id=' . $userId);
    }

    if (!validate_email($email)) {
        set_flash('error', 'Correo electrónico no válido.');
        redirect('editar_usuario.php?id=' . $userId);
    }

    if (!in_array($rolId, $roleIds, true)) {
        set_flash('error', 'El rol seleccionado no es válido.');
        redirect('editar_usuario.php?id=' . $userId);
    }

    if ($newPassword !== '' && strlen($newPassword) < 6) {
        set_flash('error', 'La contraseña debe tener al menos 6 caracteres.');
        redirect('editar_usuario.php?id=' . $userId);
    }

    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
    $check->execute([$email, $userId]);

    if ($check->fetch()) {
        set_flash('error', 'Ese correo ya está registrado por otro usuario.');
        redirect('editar_usuario.php?id=' . $userId);
    }

    if ($newPassword !== '') {
        $update = $pdo->prepare(
            'UPDATE usuarios
             SET nombre = ?, email = ?, rol_id = ?, password = ?
             WHERE id = ?'
        );
        $update->execute([$nombre, $email, $rolId, password_hash($newPassword, PASSWORD_BCRYPT), $userId]);
    } else {
        $update = $pdo->prepare(
            'UPDATE usuarios
             SET nombre = ?, email = ?, rol_id = ?
             WHERE id = ?'
        );
        $update->execute([$nombre, $email, $rolId, $userId]);
    }

    register_log(
        $pdo,
        (int)$_SESSION['id_usuario'],
        'USUARIO_EDITADO',
        'Usuario editado por administrador ID ' . $userId
    );
    set_flash('success', 'Usuario actualizado correctamente.');
    redirect('usuarios.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width: 720px; margin: 0 auto;">
    <h2>Editar usuario</h2>
    <form method="POST" id="register-form">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
        <div class="grid grid-2">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" value="<?= e($user['nombre']) ?>" required>
            </div>
            <div>
                <label for="email">Correo</label>
                <input type="email" name="email" id="email" value="<?= e($user['email']) ?>" required>
            </div>
            <div>
                <label for="password">Nueva contraseña (opcional)</label>
                <input type="password" name="password" id="password" minlength="6">
            </div>
            <div>
                <label for="rol_id">Rol</label>
                <select name="rol_id" id="rol_id" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int)$role['id'] ?>" <?= (int)$role['id'] === (int)$user['rol_id'] ? 'selected' : '' ?>>
                            <?= e($role['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-primary" type="submit">Actualizar</button>
            <a class="btn btn-secondary" href="usuarios.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
