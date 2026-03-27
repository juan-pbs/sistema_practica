# Sistema de autenticación, roles y control de acceso en PHP

## Requisitos

- XAMPP o AppServ
- PHP 8 o superior
- MySQL / MariaDB
- Extensión cURL habilitada para OAuth con Google

## Instalación

1. Copiar la carpeta `sistema_practica` dentro de `htdocs`.
2. Importar el archivo `sql/sistemapractica.sql`.
3. Verificar las credenciales en `config/conexion.php`.
4. Acceder desde `http://localhost/sistema_practica`.

## Entregables de base de datos

- Script SQL: `sql/sistemapractica.sql`
- Diagrama entidad-relación: `sql/diagrama_entidad_relacion.md`

## Usuarios de prueba

- `admin@example.com` / `password`
- `usuario@example.com` / `password`
- `vendedor@example.com` / `password`

## Estructura

- `config/`: configuración general y conexión a base de datos.
- `includes/`: funciones reutilizables, autenticación y layout.
- `admin/`: panel de usuarios y desbloqueo de cuentas.
- `productos/`: CRUD de productos.
- `ventas/`: panel para registrar salidas.
- `oauth/`: inicio de sesión externo con Google.

## Funcionalidades implementadas

- Login local con sesión PHP (`id_usuario`, `nombre_usuario`, `rol`, `permisos`).
- Bloqueo automático tras 3 intentos fallidos.
- Desbloqueo de cuentas por administrador con reconfirmación de contraseña.
- Control de acceso por rol y por permisos.
- CRUD completo de usuarios (Administrador).
- CRUD completo de productos (Administrador y Usuario).
- Panel de ventas para Vendedor (registro de salidas y actualización de stock).
- Validaciones frontend y backend.
- Login externo con Google OAuth.
