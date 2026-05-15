<?php
session_start();
require_once '../config/database.php';

$isLoggedIn = isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente';
$username   = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'Invitado';

// Obtener productos desde la base de datos
$stmt     = $conexion->query("SELECT * FROM productos ORDER BY nombre ASC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dulcería Cinépolis - Antoja algo delicioso antes de la función">
    <title>Dulcería Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- HEADER -->
<header class="app-header">
    <a href="clienteTienda.php" class="logo">Cinépolis <span>Dulcería</span></a>
    <div class="header-actions">
        <span class="header-user">Hola, <?= $username ?></span>
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
        <?php if ($isLoggedIn): ?>
            <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php?redirect=clienteTienda.php" class="btn-header-login">Iniciar Sesión</a>
        <?php endif; ?>
    </div>
</header>

<!-- LAYOUT PRINCIPAL -->
<div class="store-layout">

    <!-- ÁREA DE PRODUCTOS -->
    <div class="products-area">
        <h2>🍿 Elige tu antojo</h2>
        <div class="grid-productos">
            <?php foreach ($productos as $prod): ?>
            <div class="card-producto">
                <img src="../assets/img/<?= htmlspecialchars($prod['imagen']) ?>"
                     alt="<?= htmlspecialchars($prod['nombre']) ?>"
                     onerror="this.src='https://via.placeholder.com/300x160/1a365d/fbd304?text=🍿'">
                <div class="card-body">
                    <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                    <div class="precio">$<?= number_format($prod['precio'], 2) ?></div>
                    <div class="stock-info">Disponibles: <?= $prod['stock'] ?> uds.</div>
                    <button class="btn-agregar"
                        onclick="agregarAlCarrito(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nombre'])) ?>', <?= $prod['precio'] ?>, <?= $prod['stock'] ?>)">
                        + Agregar al carrito
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SIDEBAR DEL CARRITO -->
    <aside class="carrito-sidebar">
        <div class="carrito-header">
            <h2>🛒 Tu Carrito <span class="cart-count" id="cart-count">0</span></h2>
        </div>

        <div class="carrito-items" id="carrito-contenedor">
            <div class="carrito-vacio">
                <div class="cart-icon-empty">🛒</div>
                <span>Tu carrito está vacío.<br>¡Agrega algo delicioso!</span>
            </div>
        </div>

        <div class="carrito-footer">
            <?php if(isset($_GET['compra']) && $_GET['compra'] === 'exitosa'): ?>
            <div class="alert-success">✅ ¡Compra realizada con éxito! Gracias.</div>
            <?php endif; ?>

            <div class="total-row">
                <span class="total-label">Total a pagar</span>
                <span class="total-precio" id="carrito-total">$0.00</span>
            </div>

            <form id="form-pago" action="../controllers/PagarController.php" method="POST">
                <input type="hidden" name="carrito" id="carrito-input">
                <button type="button" class="btn-pagar" onclick="procesarPago()">
                    💳 Pagar Ahora
                </button>
            </form>
            <button class="btn-vaciar" onclick="vaciarCarrito()">Vaciar carrito</button>
        </div>
    </aside>

</div>

<script>
const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

function renderCarrito() {
    const contenedor = document.getElementById('carrito-contenedor');
    const totalEl    = document.getElementById('carrito-total');
    const countEl    = document.getElementById('cart-count');

    if (carrito.length === 0) {
        contenedor.innerHTML = `
            <div class="carrito-vacio">
                <div class="cart-icon-empty">🛒</div>
                <span>Tu carrito está vacío.<br>¡Agrega algo delicioso!</span>
            </div>`;
        totalEl.textContent = '$0.00';
        countEl.textContent = '0';
        return;
    }

    let total = 0;
    let totalItems = 0;
    let html = '';

    carrito.forEach((item, idx) => {
        const subtotal = item.precio * item.cantidad;
        total      += subtotal;
        totalItems += item.cantidad;
        html += `
        <div class="carrito-item" id="item-${idx}">
            <div class="item-info">
                <div class="item-nombre">${item.nombre}</div>
                <div class="item-precio">$${item.precio.toFixed(2)} c/u</div>
                <div class="item-controles">
                    <button class="btn-qty" onclick="cambiarCantidad(${idx}, -1)">−</button>
                    <span class="item-qty">${item.cantidad}</span>
                    <button class="btn-qty" onclick="cambiarCantidad(${idx}, 1)">+</button>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div class="item-subtotal">$${subtotal.toFixed(2)}</div>
                <button class="btn-eliminar-item" onclick="eliminarItem(${idx})" title="Quitar">✕</button>
            </div>
        </div>`;
    });

    contenedor.innerHTML = html;
    totalEl.textContent  = '$' + total.toFixed(2);
    countEl.textContent  = totalItems;
    localStorage.setItem('carrito', JSON.stringify(carrito));
}

function agregarAlCarrito(id, nombre, precio, stock) {
    const idx = carrito.findIndex(i => i.id === id);
    if (idx > -1) {
        if (carrito[idx].cantidad < stock) {
            carrito[idx].cantidad++;
        } else {
            alert('No hay más unidades disponibles.');
            return;
        }
    } else {
        carrito.push({ id, nombre, precio, cantidad: 1 });
    }
    renderCarrito();
}

function cambiarCantidad(idx, delta) {
    carrito[idx].cantidad += delta;
    if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
    renderCarrito();
}

function eliminarItem(idx) {
    carrito.splice(idx, 1);
    renderCarrito();
}

function vaciarCarrito() {
    if (carrito.length === 0) return;
    if (confirm('¿Vaciar el carrito?')) {
        carrito = [];
        renderCarrito();
    }
}

function procesarPago() {
    if (carrito.length === 0) { alert('Tu carrito está vacío.'); return; }
    if (!IS_LOGGED_IN) {
        window.location.href = 'login.php?redirect=clienteTienda.php';
    } else {
        document.getElementById('carrito-input').value = JSON.stringify(carrito);
        document.getElementById('form-pago').submit();
    }
}

/* --- Tema claro/oscuro --- */
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

/* --- Vaciar localStorage si hubo compra exitosa --- */
<?php if(isset($_GET['compra']) && $_GET['compra'] === 'exitosa'): ?>
localStorage.removeItem('carrito');
carrito = [];
<?php endif; ?>

// Render inicial
renderCarrito();
</script>

</body>
</html>