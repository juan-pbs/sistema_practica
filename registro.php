<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/autenticacion.php';

$roleStmt = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'Usuario' LIMIT 1");
$roleStmt->execute();
$defaultUserRoleId = (int)$roleStmt->fetchColumn();

if ($defaultUserRoleId <= 0) {
    set_flash('error', 'No fue posible registrar usuarios en este momento.');
    redirect_to_app('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'La sesión del formulario expiró. Intenta de nuevo.');
        redirect_to_app('registro.php');
    }

    $nombre = post('nombre');
    $email = post('email');
    $password = post('password');

    if ($nombre === '' || $email === '' || $password === '') {
        set_flash('error', 'Todos los campos son obligatorios.');
        redirect_to_app('registro.php');
    }

    if (!validate_email($email)) {
        set_flash('error', 'Correo electrónico no válido.');
        redirect_to_app('registro.php');
    }

    if (strlen($password) < 6) {
        set_flash('error', 'La contraseña debe tener al menos 6 caracteres.');
        redirect_to_app('registro.php');
    }

    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $check->execute([$email]);

    if ($check->fetch()) {
        set_flash('error', 'Ese correo ya está registrado.');
        redirect_to_app('registro.php');
    }

    $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password, rol_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_BCRYPT), $defaultUserRoleId]);

    register_log($pdo, (int)$pdo->lastInsertId(), 'REGISTRO', 'Nuevo usuario registrado');
    set_flash('success', 'Usuario registrado correctamente.');
    redirect_to_app('index.php');
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width: 640px; margin: 0 auto;">
    <h2>Registro de usuarios</h2>
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
        </div>
        <div class="form-actions">
            <button class="btn-primary" type="submit">Crear cuenta</button>
            <a class="btn btn-secondary" href="index.php">Volver</a>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
