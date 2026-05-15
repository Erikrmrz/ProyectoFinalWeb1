<?php
/**
 * ImagenController.php — Sirve imágenes almacenadas como BLOB en la BD.
 * Uso: ImagenController.php?tipo=pelicula&id=1
 *      ImagenController.php?tipo=producto&id=2
 */
require_once '../config/database.php';

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0 || !in_array($tipo, ['pelicula', 'producto'])) {
    http_response_code(400);
    exit('Parámetros inválidos');
}

try {
    if ($tipo === 'pelicula') {
        $stmt = $conexion->prepare("SELECT imagen_blob, imagen_tipo FROM peliculas WHERE id = ?");
    } else {
        $stmt = $conexion->prepare("SELECT imagen_blob, imagen_tipo FROM productos WHERE id = ?");
    }
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['imagen_blob'])) {
        $mime = $row['imagen_tipo'] ?: 'image/jpeg';
        header("Content-Type: $mime");
        header("Cache-Control: public, max-age=86400");
        echo $row['imagen_blob'];
    } else {
        // Fallback: enviar placeholder
        http_response_code(404);
        header("Content-Type: image/svg+xml");
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#1a365d" width="200" height="200"/><text fill="#fbd304" x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="40">🎬</text></svg>';
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Error del servidor');
}
?>
