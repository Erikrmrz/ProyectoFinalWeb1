<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../views/login.php"); exit();
}
require_once '../config/database.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : '');

// AGREGAR HORARIO
if ($accion === 'agregar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pelicula_id      = (int)$_POST['pelicula_id'];
    $hora             = trim($_POST['hora']);
    $precio           = (float)$_POST['precio'];
    $sala             = trim($_POST['sala']);
    $asientos_totales = (int)$_POST['asientos_totales'];

    $stmt = $conexion->prepare("INSERT INTO horarios (pelicula_id, hora, precio, sala, asientos_totales) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$pelicula_id, $hora, $precio, $sala, $asientos_totales]);

    header("Location: ../views/adminHorarios.php?ok=horario_agregado");
    exit();
}

// ELIMINAR HORARIO
if ($accion === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM horarios WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../views/adminHorarios.php?ok=horario_eliminado");
    exit();
}

header("Location: ../views/adminHorarios.php");
exit();
?>
