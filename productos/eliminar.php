<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador', 'Usuario']);
require_permission('productos.eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
    redirect('index.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Producto no válido para eliminar.');
    redirect('index.php');
}

$stmt = $pdo->prepare('SELECT id FROM productos WHERE id = ? LIMIT 1');
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    set_flash('error', 'Producto no encontrado.');
    redirect('index.php');
}

$stmt = $pdo->prepare('DELETE FROM productos WHERE id = ?');
$stmt->execute([$id]);

register_log($pdo, (int)$_SESSION['id_usuario'], 'PRODUCTO_ELIMINADO', 'Producto eliminado ID ' . $id);
set_flash('success', 'Producto eliminado.');
redirect('index.php');
