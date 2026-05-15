<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php"); exit();
}
require_once '../config/database.php';

// Eliminar producto
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([(int)$_GET['eliminar']]);
    header("Location: adminPanel.php?ok=eliminado"); exit();
}

// Obtener productos desde la BD
$stmt     = $conexion->query("SELECT * FROM productos ORDER BY id ASC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Dulcería Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="panel-layout">

<nav class="panel-nav">
    <span class="panel-nav-title">Panel de Control: <strong>Administrador</strong> — <?= htmlspecialchars($_SESSION['username']) ?></span>
    <div class="panel-nav-actions">
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
        <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
    </div>
</nav>

<div class="panel-content">
    <h2>Gestión de Productos</h2>
    <p class="desc">Administra el inventario de la dulcería. Aquí puedes ver y eliminar productos.</p>

    <?php if(isset($_GET['ok'])): ?>
        <div class="alert-success" style="margin-bottom: 20px;">✅ Producto eliminado correctamente.</div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Producto</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $prod): ?>
            <tr>
                <td><?= $prod['id'] ?></td>
                <td><img src="../assets/img/<?= htmlspecialchars($prod['imagen']) ?>" width="48" height="48"
                         style="object-fit:contain; border-radius:6px; background:var(--bg-primary);"
                         onerror="this.src='https://via.placeholder.com/48/1a365d/fbd304?text=🍿'"></td>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td>$<?= number_format($prod['precio'], 2) ?></td>
                <td>
                    <span class="badge-stock <?= $prod['stock'] > 5 ? 'ok' : 'low' ?>">
                        <?= $prod['stock'] ?> uds.
                    </span>
                </td>
                <td>
                    <a href="adminPanel.php?eliminar=<?= $prod['id'] ?>" class="btn-delete"
                       onclick="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($prod['nombre'])) ?>»?')">
                        Eliminar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const toggle = document.getElementById('themeToggle');
const html   = document.documentElement;
const saved  = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', saved);
toggle.checked = (saved === 'light');
toggle.addEventListener('change', () => {
    const t = toggle.checked ? 'light' : 'dark';
    html.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
});
</script>

</body>
</html>