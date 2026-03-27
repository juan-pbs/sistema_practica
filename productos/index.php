<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador', 'Usuario', 'Vendedor']);
require_permission('productos.ver');

$products = $pdo->query('SELECT * FROM productos ORDER BY id DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <h2>Gestión de productos</h2>
        <?php if (has_role(['Administrador', 'Usuario'])): ?>
            <a class="btn btn-success" href="crear.php">Nuevo producto</a>
        <?php endif; ?>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= (int)$product['id'] ?></td>
                        <td><?= e($product['nombre']) ?></td>
                        <td><?= e($product['descripcion']) ?></td>
                        <td>$<?= number_format((float)$product['precio'], 2) ?></td>
                        <td><?= (int)$product['stock'] ?></td>
                        <td class="actions">
                            <?php if (has_role(['Administrador', 'Usuario'])): ?>
                                <a class="btn btn-primary" href="editar.php?id=<?= (int)$product['id'] ?>">Editar</a>
                                <form method="POST" action="eliminar.php">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                    <button
                                        class="btn btn-danger"
                                        type="submit"
                                        onclick="return confirm('¿Eliminar producto?');"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span>Solo consulta</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
