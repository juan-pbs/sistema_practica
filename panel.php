<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_login();

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$totalBlocked = (int)$pdo->query('SELECT COUNT(*) FROM usuarios WHERE bloqueado = 1')->fetchColumn();
$totalLogs = (int)$pdo->query('SELECT COUNT(*) FROM logs')->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h2>Dashboard</h2>
    <p>
        Bienvenido, <strong><?= e($_SESSION['nombre_usuario']) ?></strong>.
        Rol actual: <strong><?= e($_SESSION['rol']) ?></strong>.
    </p>

    <div class="stats">
        <div class="stat">
            <strong>Usuarios</strong>
            <div><?= $totalUsers ?></div>
        </div>
        <div class="stat">
            <strong>Productos</strong>
            <div><?= $totalProducts ?></div>
        </div>
        <div class="stat">
            <strong>Cuentas bloqueadas</strong>
            <div><?= $totalBlocked ?></div>
        </div>
        <div class="stat">
            <strong>Registros en logs</strong>
            <div><?= $totalLogs ?></div>
        </div>
    </div>
</div>

<div class="card">
    <h3>Acciones disponibles</h3>
    <ul>
        <?php if (has_permission('usuarios.ver')): ?>
            <li>Administrar usuarios y desbloquear cuentas.</li>
        <?php endif; ?>
        <?php if (has_permission('productos.ver')): ?>
            <li>Consultar productos.</li>
        <?php endif; ?>
        <?php if (has_permission('productos.crear') || has_permission('productos.editar') || has_permission('productos.eliminar')): ?>
            <li>Gestionar productos (alta, edición y eliminación según tus permisos).</li>
        <?php endif; ?>
        <?php if (has_permission('ventas.registrar')): ?>
            <li>Registrar salidas y actualizar stock.</li>
        <?php endif; ?>
    </ul>
</div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
