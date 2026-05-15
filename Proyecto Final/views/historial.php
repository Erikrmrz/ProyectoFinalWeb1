<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php?redirect=historial.php"); exit();
}

$usuario_id = $_SESSION['user_id'];
$username   = htmlspecialchars($_SESSION['username']);

// Historial de boletos
$stmtBoletos = $conexion->prepare(
    "SELECT b.id, b.total, b.fecha, b.qr_data, h.hora, h.sala, p.titulo, p.clasificacion,
            GROUP_CONCAT(CONCAT(ba.fila, ba.numero) ORDER BY ba.fila, ba.numero SEPARATOR ', ') as asientos
     FROM boletos b
     JOIN horarios h ON b.horario_id = h.id
     JOIN peliculas p ON h.pelicula_id = p.id
     JOIN boletos_asientos ba ON ba.boleto_id = b.id
     WHERE b.usuario_id = ?
     GROUP BY b.id
     ORDER BY b.fecha DESC"
);
$stmtBoletos->execute([$usuario_id]);
$boletos = $stmtBoletos->fetchAll(PDO::FETCH_ASSOC);

// Historial de compras de dulcería
$stmtVentas = $conexion->prepare(
    "SELECT v.id, v.total, v.fecha,
            GROUP_CONCAT(CONCAT(p.nombre, ' x', vd.cantidad) SEPARATOR ', ') as productos
     FROM ventas v
     JOIN ventas_detalles vd ON vd.venta_id = v.id
     JOIN productos p ON vd.producto_id = p.id
     WHERE v.usuario_id = ?
     GROUP BY v.id
     ORDER BY v.fecha DESC"
);
$stmtVentas->execute([$usuario_id]);
$ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historial - Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header cine-navbar">
    <div class="nav-left">
        <a href="cartelera.php" class="logo-cinepolis">cinépolis</a>
        <nav class="main-menu">
            <a href="cartelera.php" class="menu-link">Películas</a>
            <a href="clienteTienda.php" class="menu-link">Alimentos</a>
            <a href="historial.php" class="menu-link active">Historial</a>
        </nav>
    </div>
    <div class="header-actions">
        <span class="header-user">Hola, <?= $username ?></span>
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema"><input type="checkbox" id="themeToggle"><span class="slider"></span></label>
        </div>
        <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
    </div>
</header>

<main class="historial-page">
    <h1>📋 Mi Historial</h1>

    <!-- TABS -->
    <div class="historial-tabs">
        <button class="htab active" onclick="showTab('boletos')">🎟️ Mis Boletos</button>
        <button class="htab" onclick="showTab('compras')">🛒 Mis Compras</button>
    </div>

    <!-- TAB: BOLETOS -->
    <div class="htab-content" id="tab-boletos">
        <?php if (count($boletos) === 0): ?>
            <div class="historial-empty">No tienes boletos comprados aún.</div>
        <?php else: ?>
            <?php foreach ($boletos as $b): ?>
            <div class="historial-card">
                <div class="historial-card-header">
                    <h3>🎬 <?= htmlspecialchars($b['titulo']) ?> <span class="badge-clasificacion"><?= htmlspecialchars($b['clasificacion']) ?></span></h3>
                    <span class="historial-date"><?= date('d/m/Y H:i', strtotime($b['fecha'])) ?></span>
                </div>
                <div class="historial-card-body">
                    <span>🕐 <?= date('H:i', strtotime($b['hora'])) ?> hrs</span>
                    <span>📍 <?= htmlspecialchars($b['sala']) ?></span>
                    <span>💺 <?= $b['asientos'] ?></span>
                    <span class="historial-total">$<?= number_format($b['total'], 2) ?></span>
                </div>
                <div class="historial-card-qr">
                    <button class="btn-ver-qr" onclick="toggleQR(<?= $b['id'] ?>)">📱 Ver QR</button>
                    <div class="qr-container" id="qr-<?= $b['id'] ?>" style="display:none;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($b['qr_data']) ?>"
                             alt="QR" class="qr-image-small">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- TAB: COMPRAS -->
    <div class="htab-content" id="tab-compras" style="display:none;">
        <?php if (count($ventas) === 0): ?>
            <div class="historial-empty">No tienes compras de dulcería aún.</div>
        <?php else: ?>
            <?php foreach ($ventas as $v): ?>
            <div class="historial-card">
                <div class="historial-card-header">
                    <h3>🍿 Compra #<?= $v['id'] ?></h3>
                    <span class="historial-date"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></span>
                </div>
                <div class="historial-card-body">
                    <span><?= htmlspecialchars($v['productos']) ?></span>
                    <span class="historial-total">$<?= number_format($v['total'], 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function showTab(tab) {
    document.getElementById('tab-boletos').style.display = tab === 'boletos' ? 'block' : 'none';
    document.getElementById('tab-compras').style.display = tab === 'compras' ? 'block' : 'none';
    document.querySelectorAll('.htab').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

function toggleQR(id) {
    const el = document.getElementById('qr-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

const toggle = document.getElementById('themeToggle');
const html = document.documentElement;
const saved = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', saved);
toggle.checked = (saved === 'light');
toggle.addEventListener('change', () => { const t = toggle.checked ? 'light' : 'dark'; html.setAttribute('data-theme', t); localStorage.setItem('theme', t); });
</script>
</body>
</html>
