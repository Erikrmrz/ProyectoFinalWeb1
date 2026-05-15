CREATE DATABASE IF NOT EXISTS proyectofinalweb1;
USE proyectofinalweb1;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'empleado', 'cliente') NOT NULL DEFAULT 'cliente'
);

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255)
);

-- Insertar datos iniciales de usuarios (basados en usuarios.xml)
INSERT INTO usuarios (id, username, password, rol) VALUES
(1, 'erik_admin', 'admin123', 'administrador'),
(2, 'alan_admin', 'root1234', 'administrador'),
(3, 'cliente1', 'cine123', 'cliente')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insertar datos iniciales de productos (basados en productos.xml)
INSERT INTO productos (id, nombre, precio, stock, imagen) VALUES
(1, 'Palomitas de Mantequilla (Grandes)', 85.00, 50, 'palomitas.jpg'),
(2, 'Nachos con Queso', 65.00, 30, 'nachos.jpg')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ventas_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);
