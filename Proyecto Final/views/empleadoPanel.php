<?php
session_start();
if($_SESSION['rol'] != 'empleado') { header("Location: login.php"); exit(); }
$productos = simplexml_load_file('../models/productos.xml');
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/style.css"></head>
<body style="display:block; padding: 20px;">
    <h2>Panel de Empleado - Inventario</h2>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <?php foreach ($productos->producto as $prod): ?>
            <div style="background: #1a365d; padding: 15px; border-radius: 5px;">
                <img src="../assets/img/<?= $prod->imagen ?>" width="100">
                <h3><?= $prod->nombre ?></h3>
                <p>Stock: <?= $prod->stock ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>