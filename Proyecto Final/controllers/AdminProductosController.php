<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../views/login.php"); exit();
}
require_once '../config/database.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : '');

// AGREGAR PRODUCTO
if ($accion === 'agregar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $precio = (float)$_POST['precio'];
    $stock  = (int)$_POST['stock'];

    $imagen_blob = null;
    $imagen_tipo = null;
    $imagen_nombre = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_blob   = file_get_contents($_FILES['imagen']['tmp_name']);
        $imagen_tipo   = $_FILES['imagen']['type'];
        $imagen_nombre = $_FILES['imagen']['name'];
    }

    $stmt = $conexion->prepare("INSERT INTO productos (nombre, precio, stock, imagen, imagen_blob, imagen_tipo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $precio, $stock, $imagen_nombre, $imagen_blob, $imagen_tipo]);

    header("Location: ../views/adminPanel.php?ok=producto_agregado");
    exit();
}

// EDITAR PRODUCTO
if ($accion === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $precio = (float)$_POST['precio'];
    $stock  = (int)$_POST['stock'];

    // Si se sube nueva imagen, actualizarla; si no, dejar la anterior
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_blob   = file_get_contents($_FILES['imagen']['tmp_name']);
        $imagen_tipo   = $_FILES['imagen']['type'];
        $imagen_nombre = $_FILES['imagen']['name'];
        $stmt = $conexion->prepare("UPDATE productos SET nombre=?, precio=?, stock=?, imagen=?, imagen_blob=?, imagen_tipo=? WHERE id=?");
        $stmt->execute([$nombre, $precio, $stock, $imagen_nombre, $imagen_blob, $imagen_tipo, $id]);
    } else {
        $stmt = $conexion->prepare("UPDATE productos SET nombre=?, precio=?, stock=? WHERE id=?");
        $stmt->execute([$nombre, $precio, $stock, $id]);
    }

    header("Location: ../views/adminPanel.php?ok=producto_editado");
    exit();
}

// ELIMINAR PRODUCTO
if ($accion === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../views/adminPanel.php?ok=producto_eliminado");
    exit();
}

header("Location: ../views/adminPanel.php");
exit();
?>
