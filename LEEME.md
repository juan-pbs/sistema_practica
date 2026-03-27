# Sistema de autenticación, roles y control de acceso en PHP

## Requisitos

- XAMPP o AppServ
- PHP 8 o superior
- MySQL / MariaDB
- Extensión cURL habilitada para OAuth con Google

## Instalación

1. Copiar la carpeta `sistema_practica` dentro de `htdocs`.
2. Crear `.env` a partir de `.env.example` y ajustar credenciales.
3. Importar el archivo `sql/sistemapractica.sql`.
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

## Variables de entorno

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`

## Git

- `.env` está excluido en `.gitignore`.
- Se recomienda subir únicamente `.env.example` al repositorio.
