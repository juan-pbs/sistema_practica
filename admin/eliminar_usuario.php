<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador']);
require_permission('usuarios.eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('usuarios.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
    redirect('usuarios.php');
}

$userId = (int)($_POST['user_id'] ?? 0);
$currentUserId = (int)$_SESSION['id_usuario'];

if ($userId <= 0) {
    set_flash('error', 'Usuario no válido para eliminar.');
    redirect('usuarios.php');
}

if ($userId === $currentUserId) {
    set_flash('error', 'No puedes eliminar tu propio usuario activo.');
    redirect('usuarios.php');
}

$stmt = $pdo->prepare('SELECT id, email FROM usuarios WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'El usuario que intentas eliminar no existe.');
    redirect('usuarios.php');
}

try {
    $delete = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $delete->execute([$userId]);
} catch (Throwable $e) {
    set_flash('error', 'No se pudo eliminar el usuario porque tiene registros relacionados.');
    redirect('usuarios.php');
}

register_log(
    $pdo,
    $currentUserId,
    'USUARIO_ELIMINADO',
    'Usuario eliminado por administrador: ' . $user['email']
);
set_flash('success', 'Usuario eliminado correctamente.');
redirect('usuarios.php');
