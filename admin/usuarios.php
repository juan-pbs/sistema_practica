<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/autenticacion.php';
require_role(['Administrador']);
require_permission('usuarios.ver');

$users = $pdo->query(
    'SELECT u.id, u.nombre, u.email, u.intentos_fallidos, u.bloqueado, r.nombre AS rol
     FROM usuarios u
     INNER JOIN roles r ON r.id = u.rol_id
     ORDER BY u.id DESC'
)->fetchAll();

$currentUserId = (int)$_SESSION['id_usuario'];

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <h2>Gestión de usuarios</h2>
        <a class="btn btn-success" href="crear_usuario.php">Nuevo usuario</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Intentos</th>
                    <th>Bloqueado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int)$user['id'] ?></td>
                        <td><?= e($user['nombre']) ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td><?= e($user['rol']) ?></td>
                        <td><?= (int)$user['intentos_fallidos'] ?></td>
                        <td><?= (int)$user['bloqueado'] === 1 ? 'Sí' : 'No' ?></td>
                        <td class="actions">
                            <a class="btn btn-primary" href="editar_usuario.php?id=<?= (int)$user['id'] ?>">Editar</a>

                            <?php if ((int)$user['bloqueado'] === 1): ?>
                                <form method="POST" action="desbloquear_usuario.php" class="actions">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                    <input
                                        type="password"
                                        name="admin_password"
                                        placeholder="Contraseña admin"
                                        required
                                    >
                                    <button class="btn-warning" type="submit">Desbloquear</button>
                                </form>
                            <?php endif; ?>

                            <?php if ((int)$user['id'] !== $currentUserId): ?>
                                <form method="POST" action="eliminar_usuario.php">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                    <button
                                        class="btn-danger"
                                        type="submit"
                                        onclick="return confirm('¿Eliminar este usuario?');"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span>Sesión actual</span>
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
