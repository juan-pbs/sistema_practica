<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador', 'Usuario']);
require_permission('productos.crear');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
        redirect('crear.php');
    }

    $nombre = post('nombre');
    $descripcion = post('descripcion');
    $precio = (float)($_POST['precio'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        set_flash('error', 'Verifica la información capturada.');
        redirect('crear.php');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO productos (nombre, descripcion, precio, stock, creado_por)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$nombre, $descripcion, $precio, $stock, $_SESSION['id_usuario']]);

    register_log($pdo, (int)$_SESSION['id_usuario'], 'PRODUCTO_CREADO', 'Producto registrado: ' . $nombre);
    set_flash('success', 'Producto creado correctamente.');
    redirect('index.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>Registrar producto</h2>
    <form method="POST" id="product-form">
        <?= csrf_input() ?>
        <div class="grid grid-2">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div>
                <label for="precio">Precio</label>
                <input type="number" step="0.01" name="precio" id="precio" required>
            </div>
            <div>
                <label for="stock">Stock</label>
                <input type="number" name="stock" id="stock" required>
            </div>
            <div>
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4"></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-success" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="index.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
