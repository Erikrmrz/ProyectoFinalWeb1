<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php"); exit();
}
require_once '../config/database.php';

$stmt      = $conexion->query("SELECT * FROM productos ORDER BY nombre ASC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Empleado - Dulcería Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="panel-layout">

<nav class="panel-nav">
    <span class="panel-nav-title">Panel de: <strong>Empleado</strong> — <?= htmlspecialchars($_SESSION['username']) ?></span>
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
    <h2>Inventario de Productos</h2>
    <p class="desc">Consulta el stock actual de los productos disponibles en la dulcería.</p>

    <div class="inventory-grid">
        <?php foreach ($productos as $prod): ?>
        <div class="inventory-card">
            <img src="../assets/img/<?= htmlspecialchars($prod['imagen']) ?>"
                 alt="<?= htmlspecialchars($prod['nombre']) ?>"
                 onerror="this.src='https://via.placeholder.com/100/1a365d/fbd304?text=🍿'">
            <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
            <div class="stock-badge <?= $prod['stock'] > 5 ? 'ok' : 'low' ?>"
                 style="color: <?= $prod['stock'] > 5 ? '#38a169' : '#e53e3e' ?>;">
                <?= $prod['stock'] ?>
            </div>
            <div class="stock-label">unidades en stock</div>
        </div>
        <?php endforeach; ?>
    </div>
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