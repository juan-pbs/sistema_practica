<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/autenticacion.php';

if (isset($_SESSION['id_usuario'])) {
    register_log($pdo, (int)$_SESSION['id_usuario'], 'LOGOUT', 'Cierre de sesión');
}

session_unset();
session_destroy();
session_start();

$_SESSION['clear_login_form'] = true;
set_flash('success', 'Sesión cerrada correctamente.');
redirect_to_app('index.php');
