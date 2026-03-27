<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador', 'Usuario']);
require_permission('productos.editar');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Producto no encontrado.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
        redirect('editar.php?id=' . $id);
    }

    $nombre = post('nombre');
    $descripcion = post('descripcion');
    $precio = (float)($_POST['precio'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        set_flash('error', 'Verifica la información capturada.');
        redirect('editar.php?id=' . $id);
    }

    $update = $pdo->prepare(
        'UPDATE productos
         SET nombre = ?, descripcion = ?, precio = ?, stock = ?
         WHERE id = ?'
    );
    $update->execute([$nombre, $descripcion, $precio, $stock, $id]);

    register_log($pdo, (int)$_SESSION['id_usuario'], 'PRODUCTO_EDITADO', 'Producto actualizado ID ' . $id);
    set_flash('success', 'Producto actualizado.');
    redirect('index.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>Editar producto</h2>
    <form method="POST" id="product-form">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
        <div class="grid grid-2">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" value="<?= e($product['nombre']) ?>" required>
            </div>
            <div>
                <label for="precio">Precio</label>
                <input
                    type="number"
                    step="0.01"
                    name="precio"
                    id="precio"
                    value="<?= e((string)$product['precio']) ?>"
                    required
                >
            </div>
            <div>
                <label for="stock">Stock</label>
                <input type="number" name="stock" id="stock" value="<?= e((string)$product['stock']) ?>" required>
            </div>
            <div>
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4"><?= e($product['descripcion']) ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-primary" type="submit">Actualizar</button>
            <a class="btn btn-secondary" href="index.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
