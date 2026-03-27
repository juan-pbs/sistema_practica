<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/funciones.php';

function require_login(): void
{
    if (!isset($_SESSION['id_usuario'])) {
        set_flash('error', 'Debes iniciar sesión para continuar.');
        redirect_to_app('index.php');
    }
}

function require_role(array $roles): void
{
    require_login();

    if (!has_role($roles)) {
        set_flash('error', 'Acceso no autorizado.');
        redirect_to_app('acceso_denegado.php');
    }
}

function require_permission(string $permission): void
{
    require_login();

    if (has_role(['Administrador'])) {
        return;
    }

    if (!has_permission($permission)) {
        set_flash('error', 'Acceso no autorizado.');
        redirect_to_app('acceso_denegado.php');
    }
}

function require_permissions(array $permissions): void
{
    require_login();

    if (has_role(['Administrador'])) {
        return;
    }

    foreach ($permissions as $permission) {
        if (!has_permission((string)$permission)) {
            set_flash('error', 'Acceso no autorizado.');
            redirect_to_app('acceso_denegado.php');
        }
    }
}

function get_user_with_role(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.*, r.nombre AS rol_nombre
         FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.id = ? LIMIT 1'
    );
    $stmt->execute([$userId]);

    $user = $stmt->fetch();
    return $user ?: null;
}

function current_user(PDO $pdo): ?array
{
    if (!isset($_SESSION['id_usuario'])) {
        return null;
    }

    return get_user_with_role($pdo, (int)$_SESSION['id_usuario']);
}

function get_permissions_by_role(PDO $pdo, int $roleId): array
{
    $stmt = $pdo->prepare('SELECT p.clave
                           FROM permisos p
                           INNER JOIN rol_permiso rp ON rp.permiso_id = p.id
                           WHERE rp.rol_id = ?');
    $stmt->execute([$roleId]);

    return array_column($stmt->fetchAll(), 'clave');
}

function start_user_session(PDO $pdo, array $user): void
{
    session_regenerate_id(true);
    $_SESSION['id_usuario'] = (int)$user['id'];
    $_SESSION['nombre_usuario'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol_nombre'];
    $_SESSION['permisos'] = get_permissions_by_role($pdo, (int)$user['rol_id']);
}

function register_log(PDO $pdo, ?int $userId, string $action, string $description): void
{
    $stmt = $pdo->prepare('INSERT INTO logs (usuario_id, accion, descripcion, ip_usuario)
                           VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $userId,
        $action,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);
}
