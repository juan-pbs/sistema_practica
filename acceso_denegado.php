<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacion.php';
require_login();

require_once __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h2>Acceso no autorizado</h2>
    <p>No cuentas con permisos para acceder a esta sección.</p>
    <a class="btn btn-secondary" href="panel.php">Volver al dashboard</a>
</div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
