-- ============================================================
-- Base de datos: Sistema Práctica
-- Motor: MySQL / MariaDB
-- ============================================================

DROP DATABASE IF EXISTS sistemapractica;
CREATE DATABASE sistemapractica
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sistemapractica;

-- ============================================================
-- Tablas base
-- ============================================================

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  intentos_fallidos INT NOT NULL DEFAULT 0,
  bloqueado TINYINT(1) NOT NULL DEFAULT 0,
  proveedor_oauth VARCHAR(50) NULL,
  ultimo_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles
    FOREIGN KEY (rol_id) REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE permisos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(80) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE rol_permiso (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rol_id INT NOT NULL,
  permiso_id INT NOT NULL,
  CONSTRAINT uq_rol_permiso UNIQUE (rol_id, permiso_id),
  CONSTRAINT fk_rp_role
    FOREIGN KEY (rol_id) REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm
    FOREIGN KEY (permiso_id) REFERENCES permisos(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 0,
  creado_por INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_usuario
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE salidas_productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  usuario_id INT NOT NULL,
  fecha_salida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_salidas_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_salidas_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  accion VARCHAR(80) NOT NULL,
  descripcion TEXT NOT NULL,
  ip_usuario VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_logs_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Datos semilla
-- ============================================================

INSERT INTO roles (nombre) VALUES
  ('Administrador'),
  ('Usuario'),
  ('Vendedor');

INSERT INTO permisos (clave, descripcion) VALUES
  ('usuarios.ver', 'Consultar usuarios'),
  ('usuarios.desbloquear', 'Desbloquear cuentas'),
  ('usuarios.crear', 'Crear usuarios'),
  ('usuarios.editar', 'Editar usuarios'),
  ('usuarios.eliminar', 'Eliminar usuarios'),
  ('productos.ver', 'Consultar productos'),
  ('productos.crear', 'Registrar productos'),
  ('productos.editar', 'Modificar productos'),
  ('productos.eliminar', 'Eliminar productos'),
  ('ventas.registrar', 'Registrar salidas'),
  ('stock.actualizar', 'Actualizar stock');

INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
  (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11),
  (2, 6), (2, 7), (2, 8), (2, 9),
  (3, 6), (3, 10), (3, 11);

-- Hash bcrypt de la contraseña literal: password
INSERT INTO usuarios (
  nombre,
  email,
  password,
  rol_id,
  intentos_fallidos,
  bloqueado,
  proveedor_oauth
) VALUES
  ('Administrador General', 'admin@example.com', '$2y$10$H4OgnEUZfF/ZavKe3.QRVuDBWSe/Xkcl33Gukl7I/bO6tlHGAUlRy', 1, 0, 0, NULL),
  ('Usuario Demo', 'usuario@example.com', '$2y$10$H4OgnEUZfF/ZavKe3.QRVuDBWSe/Xkcl33Gukl7I/bO6tlHGAUlRy', 2, 0, 0, NULL),
  ('Vendedor Demo', 'vendedor@example.com', '$2y$10$H4OgnEUZfF/ZavKe3.QRVuDBWSe/Xkcl33Gukl7I/bO6tlHGAUlRy', 3, 0, 0, NULL);

INSERT INTO productos (nombre, descripcion, precio, stock, creado_por) VALUES
  ('Laptop HP 240', 'Equipo de cómputo para oficina', 12500.00, 8, 1),
  ('Mouse inalámbrico', 'Periférico para escritorio', 350.00, 25, 1),
  ('Teclado mecánico', 'Teclado para uso profesional', 950.00, 12, 1);

-- Contraseña para los 3 usuarios de prueba: password
