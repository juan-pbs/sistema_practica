<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/autenticacion.php';

if (isset($_SESSION['id_usuario'])) {
    redirect_to_app('panel.php');
}

$clearLoginForm = (bool)($_SESSION['clear_login_form'] ?? false);
unset($_SESSION['clear_login_form']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post('email');
    $password = post('password');

    if ($email === '' || $password === '') {
        set_flash('error', 'Todos los campos son obligatorios.');
        redirect_to_app('index.php');
    }

    $stmt = $pdo->prepare(
        'SELECT u.*, r.nombre AS rol_nombre
         FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash('error', 'Correo o contraseña incorrectos.');
        redirect_to_app('index.php');
    }

    if ((int)$user['bloqueado'] === 1) {
        set_flash('warning', 'Tu cuenta está bloqueada. Contacta al administrador.');
        redirect_to_app('index.php');
    }

    if (!password_verify($password, $user['password'])) {
        $stmt = $pdo->prepare(
            'UPDATE usuarios
             SET intentos_fallidos = intentos_fallidos + 1
             WHERE id = ?'
        );
        $stmt->execute([$user['id']]);

        $stmt = $pdo->prepare('SELECT intentos_fallidos FROM usuarios WHERE id = ?');
        $stmt->execute([$user['id']]);
        $attempts = (int)$stmt->fetchColumn();

        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $stmt = $pdo->prepare('UPDATE usuarios SET bloqueado = 1 WHERE id = ?');
            $stmt->execute([$user['id']]);

            register_log($pdo, (int)$user['id'], 'LOGIN_BLOQUEADO', 'Cuenta bloqueada por intentos fallidos');
            set_flash('error', 'Cuenta bloqueada por 3 intentos fallidos.');
            redirect_to_app('index.php');
        }

        register_log($pdo, (int)$user['id'], 'LOGIN_FALLIDO', 'Intento fallido de inicio de sesión');
        set_flash('error', 'Correo o contraseña incorrectos.');
        redirect_to_app('index.php');
    }

    start_user_session($pdo, $user);

    $stmt = $pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0, ultimo_login = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);

    register_log($pdo, (int)$user['id'], 'LOGIN_OK', 'Inicio de sesión correcto');
    set_flash('success', 'Bienvenido al sistema.');
    redirect_to_app('panel.php');
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width: 520px; margin: 0 auto;">
    <h2>Iniciar sesión</h2>
    <form method="POST" id="login-form" autocomplete="off">
        <div class="grid">
            <div>
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" required autocomplete="off">
            </div>
            <div>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required autocomplete="off">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-primary" type="submit">Entrar</button>
            <a class="btn btn-secondary" href="registro.php">Registrarse</a>
        </div>
    </form>
    <hr>
    <p><strong>También puedes iniciar con Google:</strong></p>
    <a class="btn btn-warning" href="oauth/iniciar_google.php">Iniciar sesión con Google</a>
</div>
<?php if ($clearLoginForm): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('login-form');
            if (form) {
                form.reset();
            }
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            if (email) email.value = '';
            if (password) password.value = '';
        });
    </script>
<?php endif; ?>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
