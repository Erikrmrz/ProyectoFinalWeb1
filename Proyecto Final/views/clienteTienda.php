<?php
session_start();
// Validación de seguridad para clientes [cite: 5]
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

$productos = simplexml_load_file('../models/productos.xml');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cinépolis - Dulcería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding: 20px; }
        .card { background: #1a365d; border: 1px solid #fbd304; border-radius: 10px; padding: 15px; text-align: center; }
        .card img { max-width: 100%; border-radius: 5px; }
        .precio { color: #fbd304; font-size: 1.2em; font-weight: bold; }
        .btn-comprar { background: #fbd304; color: #0b1e36; border: none; padding: 10px; border-radius: 5px; cursor: pointer; width: 100%; }
    </style>
</head>
<body style="display:block; margin:0;">

    <header style="background: #0b1e36; padding: 20px; border-bottom: 2px solid #fbd304;">
        <h1 style="margin:0; color: #fbd304;">Dulcería Cinépolis</h1>
        <p>Hola, <?= $_SESSION['username'] ?>. ¿Qué se te antoja hoy?</p>
    </header>

    <div class="grid-productos">
        <?php foreach ($productos->producto as $prod): ?>
        <div class="card">
            <img src="../assets/img/<?= $prod->imagen ?>" alt="<?= $prod->nombre ?>">
            <h3><?= $prod->nombre ?></h3>
            <p class="precio">$<?= $prod->precio ?></p>
            <p>Disponibles: <?= $prod->stock ?></p>
            <button class="btn-comprar" onclick="alert('¡Producto añadido al carrito!')">Comprar</button>
        </div>
        <?php endforeach; ?>
    </div>

    <footer style="text-align: center; padding: 20px;">
        <a href="../controllers/LogoutController.php" style="color: white;">Salir del Sistema</a>
    </footer >
</body>
</html>