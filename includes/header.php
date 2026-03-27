<?php
require_once __DIR__ . '/funciones.php';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e(APP_NAME) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script defer src="<?= BASE_URL ?>/assets/js/validations.js"></script>
</head>
<body>
<header class="topbar">
    <div>
        <h1><?= e(APP_NAME) ?></h1>
        <p class="subtitle">Autenticación, roles y control de acceso en PHP puro</p>
    </div>

    <?php if (isset($_SESSION['id_usuario'])): ?>
        <nav class="nav">
            <a href="<?= BASE_URL ?>/panel.php">Dashboard</a>
            <?php if (has_permission('productos.ver')): ?>
                <a href="<?= BASE_URL ?>/productos/index.php">Productos</a>
            <?php endif; ?>
            <?php if (has_permission('usuarios.ver')): ?>
                <a href="<?= BASE_URL ?>/admin/usuarios.php">Usuarios</a>
            <?php endif; ?>
            <?php if (has_permission('ventas.registrar')): ?>
                <a href="<?= BASE_URL ?>/ventas/index.php">Ventas</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/salir.php">Cerrar sesión</a>
        </nav>
    <?php endif; ?>
</header>

<main class="container">
    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
