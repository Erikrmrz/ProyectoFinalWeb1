<?php
session_start();
require_once '../config/database.php';

$isLoggedIn = isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente';
$username   = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'Invitado';

$horario_id = isset($_GET['horario_id']) ? (int)$_GET['horario_id'] : 0;
$error      = isset($_GET['error']) ? $_GET['error'] : '';

if ($horario_id <= 0) { header("Location: cartelera.php"); exit(); }

// Obtener info del horario + película
$stmt = $conexion->prepare(
    "SELECT h.*, p.titulo, p.clasificacion, p.imagen, p.imagen_blob
     FROM horarios h JOIN peliculas p ON h.pelicula_id = p.id WHERE h.id = ?"
);
$stmt->execute([$horario_id]);
$funcion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$funcion) { header("Location: cartelera.php"); exit(); }

// Obtener asientos ya ocupados
$stmtOcupados = $conexion->prepare(
    "SELECT ba.fila, ba.numero FROM boletos_asientos ba
     JOIN boletos b ON ba.boleto_id = b.id WHERE b.horario_id = ?"
);
$stmtOcupados->execute([$horario_id]);
$ocupados = $stmtOcupados->fetchAll(PDO::FETCH_ASSOC);
$ocupadosMap = [];
foreach ($ocupados as $o) { $ocupadosMap[$o['fila'] . $o['numero']] = true; }

$filas    = ['A', 'B', 'C', 'D', 'E', 'F'];
$columnas = range(1, 10);
$totalOcupados = count($ocupados);
$disponibles   = $funcion['asientos_totales'] - $totalOcupados;

// Imagen
$imgSrc = !empty($funcion['imagen_blob'])
    ? '../controllers/ImagenController.php?tipo=pelicula&id=' . $funcion['pelicula_id']
    : '../assets/img/' . htmlspecialchars($funcion['imagen'] ?? '');
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Asientos - <?= htmlspecialchars($funcion['titulo']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header cine-navbar">
    <div class="nav-left">
        <a href="cartelera.php" class="logo-cinepolis">cinépolis</a>
        <nav class="main-menu">
            <a href="cartelera.php" class="menu-link">Películas</a>
            <a href="clienteTienda.php" class="menu-link">Alimentos</a>
        </nav>
    </div>
    <div class="header-actions">
        <span class="header-user">Hola, <?= $username ?></span>
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema"><input type="checkbox" id="themeToggle"><span class="slider"></span></label>
        </div>
        <?php if ($isLoggedIn): ?>
            <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php?redirect=seleccionAsientos.php?horario_id=<?= $horario_id ?>" class="btn-header-login">Iniciar Sesión</a>
        <?php endif; ?>
    </div>
</header>

<main class="seats-page">
    <div class="seats-info-panel">
        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($funcion['titulo']) ?>" class="seats-poster"
             onerror="this.src='https://via.placeholder.com/200x300/1a365d/fbd304?text=🎬'">
        <div class="seats-details">
            <h1><?= htmlspecialchars($funcion['titulo']) ?> <span class="badge-clasificacion"><?= htmlspecialchars($funcion['clasificacion']) ?></span></h1>
            <div class="seats-meta">
                <span>🕐 <?= date('H:i', strtotime($funcion['hora'])) ?></span>
                <span>📍 <?= htmlspecialchars($funcion['sala']) ?></span>
                <span>💰 $<?= number_format($funcion['precio'], 2) ?> por boleto</span>
                <span>💺 <?= $disponibles ?> asientos disponibles</span>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars(urldecode($error)) ?></div>
    <?php endif; ?>

    <!-- PANTALLA -->
    <div class="screen-indicator">PANTALLA</div>

    <!-- GRID DE ASIENTOS -->
    <div class="seats-grid-wrapper">
        <?php foreach ($filas as $fila): ?>
        <div class="seats-row">
            <span class="row-label"><?= $fila ?></span>
            <?php foreach ($columnas as $num):
                $key = $fila . $num;
                $ocupado = isset($ocupadosMap[$key]);
            ?>
            <button class="seat <?= $ocupado ? 'occupied' : 'available' ?>"
                    data-fila="<?= $fila ?>" data-numero="<?= $num ?>"
                    <?= $ocupado ? 'disabled' : 'onclick="toggleSeat(this)"' ?>
                    title="<?= $key ?>">
                <?= $num ?>
            </button>
            <?php endforeach; ?>
            <span class="row-label"><?= $fila ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- LEYENDA -->
    <div class="seats-legend">
        <span><span class="seat-sample available"></span> Disponible</span>
        <span><span class="seat-sample selected"></span> Seleccionado</span>
        <span><span class="seat-sample occupied"></span> Ocupado</span>
    </div>

    <!-- RESUMEN Y BOTÓN -->
    <div class="seats-summary">
        <div class="summary-text">
            <span>Asientos: <strong id="selected-seats">Ninguno</strong></span>
            <span>Total: <strong id="selected-total">$0.00</strong></span>
        </div>
        <form id="form-boleto" action="../controllers/ComprarBoletoController.php" method="POST">
            <input type="hidden" name="horario_id" value="<?= $horario_id ?>">
            <input type="hidden" name="asientos" id="asientos-input">
            <button type="button" class="btn-pagar btn-comprar-boleto" onclick="confirmarCompra()">
                🎟️ Comprar Boletos
            </button>
        </form>
    </div>
</main>

<script>
const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
const PRECIO = <?= $funcion['precio'] ?>;
let seleccionados = [];

function toggleSeat(btn) {
    const fila = btn.dataset.fila;
    const numero = parseInt(btn.dataset.numero);
    const key = fila + numero;
    const idx = seleccionados.findIndex(s => s.fila === fila && s.numero === numero);

    if (idx > -1) {
        seleccionados.splice(idx, 1);
        btn.classList.remove('selected');
        btn.classList.add('available');
    } else {
        seleccionados.push({ fila, numero });
        btn.classList.remove('available');
        btn.classList.add('selected');
    }
    updateSummary();
}

function updateSummary() {
    const seatsEl = document.getElementById('selected-seats');
    const totalEl = document.getElementById('selected-total');
    if (seleccionados.length === 0) {
        seatsEl.textContent = 'Ninguno';
        totalEl.textContent = '$0.00';
    } else {
        seatsEl.textContent = seleccionados.map(s => s.fila + s.numero).join(', ');
        totalEl.textContent = '$' + (seleccionados.length * PRECIO).toFixed(2);
    }
}

function confirmarCompra() {
    if (seleccionados.length === 0) { alert('Selecciona al menos un asiento.'); return; }
    if (!IS_LOGGED_IN) {
        window.location.href = 'login.php?redirect=seleccionAsientos.php?horario_id=<?= $horario_id ?>';
        return;
    }
    document.getElementById('asientos-input').value = JSON.stringify(seleccionados);
    document.getElementById('form-boleto').submit();
}

/* Tema */
const toggle = document.getElementById('themeToggle');
const html = document.documentElement;
const saved = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', saved);
toggle.checked = (saved === 'light');
toggle.addEventListener('change', () => { const t = toggle.checked ? 'light' : 'dark'; html.setAttribute('data-theme', t); localStorage.setItem('theme', t); });
</script>
</body>
</html>
