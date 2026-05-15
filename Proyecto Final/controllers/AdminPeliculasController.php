<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../views/login.php"); exit();
}
require_once '../config/database.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : '');

// AGREGAR PELÍCULA
if ($accion === 'agregar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo        = trim($_POST['titulo']);
    $clasificacion = trim($_POST['clasificacion']);

    $imagen_blob = null;
    $imagen_tipo = null;
    $imagen_nombre = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_blob   = file_get_contents($_FILES['imagen']['tmp_name']);
        $imagen_tipo   = $_FILES['imagen']['type'];
        $imagen_nombre = $_FILES['imagen']['name'];
    }

    $stmt = $conexion->prepare("INSERT INTO peliculas (titulo, clasificacion, imagen, imagen_blob, imagen_tipo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $clasificacion, $imagen_nombre, $imagen_blob, $imagen_tipo]);

    header("Location: ../views/adminPeliculas.php?ok=pelicula_agregada");
    exit();
}

// ELIMINAR PELÍCULA (cascade elimina horarios)
if ($accion === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM peliculas WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../views/adminPeliculas.php?ok=pelicula_eliminada");
    exit();
}

header("Location: ../views/adminPeliculas.php");
exit();
?>
