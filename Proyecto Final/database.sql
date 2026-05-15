CREATE DATABASE IF NOT EXISTS proyectofinalweb1;
USE proyectofinalweb1;

-- ========================================================
-- TABLA DE USUARIOS
-- ========================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'empleado', 'cliente') NOT NULL DEFAULT 'cliente'
);

-- ========================================================
-- TABLA DE PRODUCTOS (DULCERÍA) — imágenes como BLOB
-- ========================================================
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NULL,
    imagen_blob LONGBLOB NULL,
    imagen_tipo VARCHAR(50) NULL
);

-- ========================================================
-- TABLA DE VENTAS DE DULCERÍA
-- ========================================================
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

-- ========================================================
-- TABLA DE PELÍCULAS — imágenes como BLOB
-- ========================================================
CREATE TABLE IF NOT EXISTS peliculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    imagen VARCHAR(255) NULL,
    imagen_blob LONGBLOB NULL,
    imagen_tipo VARCHAR(50) NULL,
    clasificacion VARCHAR(10) NOT NULL
);

-- ========================================================
-- TABLA DE HORARIOS (con precio, sala y capacidad)
-- ========================================================
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelicula_id INT NOT NULL,
    hora TIME NOT NULL,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 75.00,
    sala VARCHAR(20) NOT NULL DEFAULT 'Sala 1',
    asientos_totales INT NOT NULL DEFAULT 60,
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
);

-- ========================================================
-- TABLA DE BOLETOS (compra de entradas)
-- ========================================================
CREATE TABLE IF NOT EXISTS boletos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    horario_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    qr_data TEXT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (horario_id) REFERENCES horarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS boletos_asientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boleto_id INT NOT NULL,
    fila CHAR(1) NOT NULL,
    numero INT NOT NULL,
    FOREIGN KEY (boleto_id) REFERENCES boletos(id) ON DELETE CASCADE
);

-- ========================================================
-- DATOS INICIALES
-- ========================================================

-- Usuarios
INSERT INTO usuarios (id, username, password, rol) VALUES
(1, 'erik_admin', 'admin123', 'administrador'),
(2, 'alan_admin', 'root1234', 'administrador'),
(3, 'cliente1', 'cine123', 'cliente')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Productos de dulcería (imagen como archivo, BLOB se agrega vía admin)
INSERT INTO productos (id, nombre, precio, stock, imagen) VALUES
(1, 'Palomitas de Mantequilla (Grandes)', 85.00, 50, 'palomitas.jpg'),
(2, 'Nachos con Queso', 65.00, 30, 'nachos.jpg')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Películas (imagen como archivo, BLOB se agrega vía admin)
INSERT INTO peliculas (id, titulo, imagen, clasificacion) VALUES
(1, 'Deadpool & Wolverine', 'deadpool.jpg', 'C'),
(2, 'Intensa-Mente 2', 'IntMente2.webp', 'AA'),
(3, 'Mi Villano Favorito 4', 'MVF4.jpg', 'A'),
(4, 'Un Lugar en Silencio: Día Uno', 'ULESDia1.jpg', 'B15')
ON DUPLICATE KEY UPDATE imagen=VALUES(imagen), titulo=VALUES(titulo);

-- Horarios con precio y sala
INSERT INTO horarios (pelicula_id, hora, precio, sala, asientos_totales) VALUES
(1, '14:30:00', 75.00, 'Sala 1', 60),
(1, '17:00:00', 85.00, 'Sala 1', 60),
(1, '19:45:00', 95.00, 'Sala 2', 60),
(1, '22:15:00', 95.00, 'Sala 2', 60),
(2, '13:00:00', 65.00, 'Sala 3', 60),
(2, '15:20:00', 75.00, 'Sala 3', 60),
(2, '18:10:00', 85.00, 'Sala 4', 60),
(3, '12:45:00', 65.00, 'Sala 5', 60),
(3, '16:15:00', 75.00, 'Sala 5', 60),
(3, '18:40:00', 85.00, 'Sala 6', 60),
(4, '20:00:00', 90.00, 'Sala 7', 60),
(4, '22:30:00', 95.00, 'Sala 7', 60);