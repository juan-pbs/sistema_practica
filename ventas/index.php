<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Vendedor']);
require_permissions(['ventas.registrar', 'stock.actualizar']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
        redirect('index.php');
    }

    $productoId = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);

    $stmt = $pdo->prepare('SELECT stock, nombre FROM productos WHERE id = ? LIMIT 1');
    $stmt->execute([$productoId]);
    $product = $stmt->fetch();

    if (!$product || $cantidad <= 0) {
        set_flash('error', 'Datos inválidos para registrar la salida.');
        redirect('index.php');
    }

    if ((int)$product['stock'] < $cantidad) {
        set_flash('error', 'No hay stock suficiente.');
        redirect('index.php');
    }

    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare(
            'INSERT INTO salidas_productos (producto_id, cantidad, usuario_id)
             VALUES (?, ?, ?)'
        );
        $insert->execute([$productoId, $cantidad, $_SESSION['id_usuario']]);

        $update = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
        $update->execute([$cantidad, $productoId]);

        register_log(
            $pdo,
            (int)$_SESSION['id_usuario'],
            'SALIDA_PRODUCTO',
            'Salida de ' . $cantidad . ' unidades del producto ' . $product['nombre']
        );

        $pdo->commit();
        set_flash('success', 'Salida registrada correctamente.');
        redirect('index.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        set_flash('error', 'No fue posible registrar la salida.');
        redirect('index.php');
    }
}

$products = $pdo->query('SELECT id, nombre, stock FROM productos ORDER BY nombre')->fetchAll();
$sales = $pdo->query(
    'SELECT s.id, p.nombre, s.cantidad, s.fecha_salida
     FROM salidas_productos s
     INNER JOIN productos p ON p.id = s.producto_id
     ORDER BY s.id DESC'
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>Panel de ventas</h2>
    <form method="POST">
        <?= csrf_input() ?>
        <div class="grid grid-2">
            <div>
                <label for="producto_id">Producto</label>
                <select name="producto_id" id="producto_id" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int)$product['id'] ?>">
                            <?= e($product['nombre']) ?> (Stock: <?= (int)$product['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cantidad">Cantidad a retirar</label>
                <input type="number" name="cantidad" id="cantidad" min="1" required>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-success" type="submit">Registrar salida</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Historial de salidas</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= (int)$sale['id'] ?></td>
                        <td><?= e($sale['nombre']) ?></td>
                        <td><?= (int)$sale['cantidad'] ?></td>
                        <td><?= e($sale['fecha_salida']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
