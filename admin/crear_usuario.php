<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador']);
require_permission('usuarios.crear');

$roles = $pdo->query('SELECT id, nombre FROM roles ORDER BY nombre')->fetchAll();
$roleIds = array_map(static fn(array $role): int => (int)$role['id'], $roles);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
        redirect('crear_usuario.php');
    }

    $nombre = post('nombre');
    $email = post('email');
    $password = post('password');
    $rolId = (int)($_POST['rol_id'] ?? 0);

    if ($nombre === '' || $email === '' || $password === '' || $rolId <= 0) {
        set_flash('error', 'Todos los campos son obligatorios.');
        redirect('crear_usuario.php');
    }

    if (!validate_email($email)) {
        set_flash('error', 'Correo electrónico no válido.');
        redirect('crear_usuario.php');
    }

    if (strlen($password) < 6) {
        set_flash('error', 'La contraseña debe tener al menos 6 caracteres.');
        redirect('crear_usuario.php');
    }

    if (!in_array($rolId, $roleIds, true)) {
        set_flash('error', 'El rol seleccionado no es válido.');
        redirect('crear_usuario.php');
    }

    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $check->execute([$email]);

    if ($check->fetch()) {
        set_flash('error', 'Ese correo ya está registrado.');
        redirect('crear_usuario.php');
    }

    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nombre, email, password, rol_id)
         VALUES (?, ?, ?, ?)'
    );
    $insert->execute([$nombre, $email, password_hash($password, PASSWORD_BCRYPT), $rolId]);

    register_log(
        $pdo,
        (int)$_SESSION['id_usuario'],
        'USUARIO_CREADO',
        'Usuario creado por administrador: ' . $email
    );
    set_flash('success', 'Usuario creado correctamente.');
    redirect('usuarios.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width: 720px; margin: 0 auto;">
    <h2>Crear usuario</h2>
    <form method="POST" id="register-form">
        <?= csrf_input() ?>
        <div class="grid grid-2">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div>
                <label for="email">Correo</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required minlength="6">
            </div>
            <div>
                <label for="rol_id">Rol</label>
                <select name="rol_id" id="rol_id" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int)$role['id'] ?>"><?= e($role['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-success" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="usuarios.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
