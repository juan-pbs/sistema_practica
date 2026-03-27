<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador']);
require_permission('usuarios.desbloquear');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('usuarios.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
    redirect('usuarios.php');
}

$userId = (int)($_POST['user_id'] ?? 0);
$adminPassword = post('admin_password');

if ($userId <= 0 || $adminPassword === '') {
    set_flash('error', 'Datos incompletos para desbloquear la cuenta.');
    redirect('usuarios.php');
}

$admin = current_user($pdo);
if (!$admin || !password_verify($adminPassword, $admin['password'])) {
    set_flash('error', 'La contraseña del administrador no es válida.');
    redirect('usuarios.php');
}

$stmt = $pdo->prepare(
    'UPDATE usuarios
     SET bloqueado = 0, intentos_fallidos = 0
     WHERE id = ?'
);
$stmt->execute([$userId]);

register_log(
    $pdo,
    (int)$admin['id'],
    'DESBLOQUEO_USUARIO',
    'Se desbloqueó la cuenta del usuario ID ' . $userId
);
set_flash('success', 'Cuenta desbloqueada correctamente.');
redirect('usuarios.php');
