<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) { header("Location: cartelera.php"); exit(); }

$boleto_id = isset($_GET['boleto_id']) ? (int)$_GET['boleto_id'] : 0;
if ($boleto_id <= 0) { header("Location: cartelera.php"); exit(); }

// Obtener boleto
$stmt = $conexion->prepare(
    "SELECT b.*, h.hora, h.sala, h.precio, p.titulo, p.clasificacion
     FROM boletos b
     JOIN horarios h ON b.horario_id = h.id
     JOIN peliculas p ON h.pelicula_id = p.id
     WHERE b.id = ? AND b.usuario_id = ?"
);
$stmt->execute([$boleto_id, $_SESSION['user_id']]);
$boleto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$boleto) { header("Location: cartelera.php"); exit(); }

// Obtener asientos
$stmtA = $conexion->prepare("SELECT fila, numero FROM boletos_asientos WHERE boleto_id = ? ORDER BY fila, numero");
$stmtA->execute([$boleto_id]);
$asientos = $stmtA->fetchAll(PDO::FETCH_ASSOC);
$asientosStr = implode(', ', array_map(function($a) { return $a['fila'] . $a['numero']; }, $asientos));

$qrData = urlencode($boleto['qr_data']);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleto Confirmado - Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header cine-navbar">
    <div class="nav-left">
        <a href="cartelera.php" class="logo-cinepolis">cinépolis</a>
        <nav class="main-menu">
            <a href="cartelera.php" class="menu-link">Películas</a>
            <a href="clienteTienda.php" class="menu-link">Alimentos</a>
            <a href="historial.php" class="menu-link">Historial</a>
        </nav>
    </div>
    <div class="header-actions">
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema"><input type="checkbox" id="themeToggle"><span class="slider"></span></label>
        </div>
        <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
    </div>
</header>

<main class="confirmation-page">
    <div class="ticket-card">
        <div class="ticket-header">
            <div class="ticket-success-icon">✅</div>
            <h1>¡Compra Exitosa!</h1>
            <p>Tu boleto ha sido confirmado</p>
        </div>

        <div class="ticket-body">
            <div class="ticket-detail">
                <span class="label">Película</span>
                <span class="value"><?= htmlspecialchars($boleto['titulo']) ?> <span class="badge-clasificacion"><?= htmlspecialchars($boleto['clasificacion']) ?></span></span>
            </div>
            <div class="ticket-detail">
                <span class="label">Horario</span>
                <span class="value"><?= date('H:i', strtotime($boleto['hora'])) ?> hrs</span>
            </div>
            <div class="ticket-detail">
                <span class="label">Sala</span>
                <span class="value"><?= htmlspecialchars($boleto['sala']) ?></span>
            </div>
            <div class="ticket-detail">
                <span class="label">Asientos</span>
                <span class="value"><?= $asientosStr ?></span>
            </div>
            <div class="ticket-detail">
                <span class="label">Total</span>
                <span class="value ticket-total">$<?= number_format($boleto['total'], 2) ?></span>
            </div>
            <div class="ticket-detail">
                <span class="label">Fecha</span>
                <span class="value"><?= date('d/m/Y H:i', strtotime($boleto['fecha'])) ?></span>
            </div>
        </div>

        <!-- QR CODE -->
        <div class="ticket-qr">
            <p>Presenta este código QR en la entrada:</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= $qrData ?>"
                 alt="Código QR del boleto" class="qr-image">
        </div>

        <div class="ticket-actions">
            <a href="cartelera.php" class="btn-pagar" style="display:inline-block; text-decoration:none; text-align:center;">Volver a Cartelera</a>
            <a href="historial.php" class="btn-vaciar" style="display:inline-block; text-decoration:none; text-align:center; margin-top:10px;">Ver Historial</a>
        </div>
    </div>
</main>

<script>
const toggle = document.getElementById('themeToggle');
const html   = document.documentElement;
const saved  = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', saved);
toggle.checked = (saved === 'light');
toggle.addEventListener('change', () => { const t = toggle.checked ? 'light' : 'dark'; html.setAttribute('data-theme', t); localStorage.setItem('theme', t); });
</script>
</body>
</html>
