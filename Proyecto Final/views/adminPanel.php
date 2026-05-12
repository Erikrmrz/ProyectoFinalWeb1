<?php
session_start();
// Validación de seguridad por rol [cite: 5]
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

$archivo_xml = '../models/productos.xml';
$productos = simplexml_load_file($archivo_xml);

// Lógica para eliminar un producto (Parte del CRUD solicitado) [cite: 5]
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $i = 0;
    foreach ($productos->producto as $prod) {
        if ($prod->id == $id_eliminar) {
            unset($productos->producto[$i]);
            break;
        }
        $i++;
    }
    $productos->asXML($archivo_xml);
    header("Location: adminPanel.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestión de Dulcería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; color: #000; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        .btn-delete { color: red; text-decoration: none; font-weight: bold; }
        .nav-admin { background: #fbd304; padding: 10px; color: #0b1e36; display: flex; justify-content: space-between; }
    </style>
</head>
<body style="display:block; margin:0;">

    <div class="nav-admin">
        <span>Panel de Control: <strong>Administrador</strong></span>
        <a href="../controllers/LogoutController.php" style="color:#0b1e36;">Cerrar Sesión</a>
    </div>

    <div style="padding: 20px;">
        <h2>Gestión de Productos</h2>
        [cite_start]<p>Desde aquí puedes gestionar el inventario de la dulcería[cite: 1].</p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Existencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos->producto as $prod): ?>
                <tr>
                    <td><?= $prod->id ?></td>
                    <td><img src="../assets/img/<?= $prod->imagen ?>" width="50"></td>
                    <td><?= $prod->nombre ?></td>
                    <td>$<?= $prod->precio ?></td>
                    <td><?= $prod->stock ?></td>
                    <td>
                        <a href="adminPanel.php?eliminar=<?= $prod->id ?>" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminarlo?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>