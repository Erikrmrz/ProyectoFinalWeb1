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

-- ========================================================
-- NUEVAS TABLAS PARA LA CARTELERA Y HORARIOS
-- ========================================================

CREATE TABLE IF NOT EXISTS peliculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    clasificacion VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelicula_id INT NOT NULL,
    hora TIME NOT NULL,
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
);

-- Insertar datos iniciales de las películas
INSERT INTO peliculas (id, titulo, imagen, clasificacion) VALUES
(1, 'Deadpool & Wolverine', 'https://via.placeholder.com/250x350/1a365d/fff?text=Deadpool', 'C'),
(2, 'Intensa-Mente 2', 'https://via.placeholder.com/250x350/1a365d/fff?text=Intensa-Mente+2', 'AA'),
(3, 'Mi Villano Favorito 4', 'https://via.placeholder.com/250x350/1a365d/fff?text=Villano+Fav+4', 'A'),
(4, 'Un Lugar en Silencio: Día Uno', 'https://via.placeholder.com/250x350/1a365d/fff?text=Lugar+Silencio', 'B15')
ON DUPLICATE KEY UPDATE titulo=VALUES(titulo);

-- Insertar los horarios correspondientes en formato HH:MM:SS
INSERT INTO horarios (pelicula_id, hora) VALUES
(1, '14:30:00'), (1, '17:00:00'), (1, '19:45:00'), (1, '22:15:00'),
(2, '13:00:00'), (2, '15:20:00'), (2, '18:10:00'),
(3, '12:45:00'), (3, '16:15:00'), (3, '18:40:00'),
(4, '20:00:00'), (4, '22:30:00');