<?php
session_start();
require_once '../config/database.php';

// Ya no restringimos el acceso aquí, permitimos ver la tienda sin sesión
$isLoggedIn = isset($_SESSION['rol']) && $_SESSION['rol'] == 'cliente';
$username = $isLoggedIn ? $_SESSION['username'] : 'Invitado';

// Obtener productos de la base de datos MySQL
$stmt = $conexion->query("SELECT * FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cinépolis - Dulcería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; margin: 0; }
        .main-content { display: flex; flex: 1; }
        .productos-section { flex: 3; }
        .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding: 20px; }
        .card { background: #1a365d; border: 1px solid #fbd304; border-radius: 10px; padding: 15px; text-align: center; }
        .card img { max-width: 100%; border-radius: 5px; }
        .precio { color: #fbd304; font-size: 1.2em; font-weight: bold; }
        .btn-comprar { background: #fbd304; color: #0b1e36; border: none; padding: 10px; border-radius: 5px; cursor: pointer; width: 100%; }
        
        .carrito-section { flex: 1; background: #f4f4f4; padding: 20px; border-left: 2px solid #ddd; display: flex; flex-direction: column; }
        .carrito-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .carrito-item-info { flex: 1; }
        .carrito-total { font-weight: bold; font-size: 1.2em; margin-top: 20px; text-align: right; }
        .btn-pagar { background: #28a745; color: white; border: none; padding: 15px; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 20px; font-size: 1.1em; }
        .btn-pagar:hover { background: #218838; }
    </style>
</head>
<body>

    <header style="background: #0b1e36; padding: 20px; border-bottom: 2px solid #fbd304; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin:0; color: #fbd304;">Dulcería Cinépolis</h1>
            <p style="color: white; margin-top: 5px;">Hola, <?= htmlspecialchars($username) ?>. ¿Qué se te antoja hoy?</p>
        </div>
        <div>
            <?php if ($isLoggedIn): ?>
                <a href="../controllers/LogoutController.php" style="color: white; text-decoration: none; border: 1px solid white; padding: 8px; border-radius: 5px;">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php?redirect=clienteTienda.php" style="color: #0b1e36; background: #fbd304; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-weight: bold;">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-content">
        <div class="productos-section">
            <div class="grid-productos">
                <?php foreach ($productos as $prod): ?>
                <div class="card">
                    <img src="../assets/img/<?= htmlspecialchars($prod['imagen']) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" onerror="this.src='../assets/img/placeholder.jpg'">
                    <h3 style="color: white;"><?= htmlspecialchars($prod['nombre']) ?></h3>
                    <p class="precio">$<?= number_format($prod['precio'], 2) ?></p>
                    <p style="color: white;">Disponibles: <span id="stock-<?= $prod['id'] ?>"><?= $prod['stock'] ?></span></p>
                    <button class="btn-comprar" onclick="agregarAlCarrito(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nombre'])) ?>', <?= $prod['precio'] ?>)">Agregar al Carrito</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="carrito-section">
            <h2 style="margin-top: 0;">Tu Carrito</h2>
            <div id="carrito-contenedor">
                <!-- Items del carrito irán aquí -->
            </div>
            <div class="carrito-total">
                Total: $<span id="carrito-total-precio">0.00</span>
            </div>
            
            <!-- Mensaje de éxito si regresa de una compra exitosa -->
            <?php if(isset($_GET['compra']) && $_GET['compra'] == 'exitosa'): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; margin-top: 15px; border-radius: 5px;">
                    ¡Compra realizada con éxito! Gracias por tu preferencia.
                </div>
                <script>
                    // Vaciar carrito del localstorage
                    localStorage.removeItem('carrito');
                </script>
            <?php endif; ?>

            <form id="form-pago" action="../controllers/PagarController.php" method="POST">
                <input type="hidden" name="carrito" id="carrito-input">
                <button type="button" class="btn-pagar" onclick="procesarPago()">Pagar Ahora</button>
            </form>
        </div>
    </div>

    <script>
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

        function renderizarCarrito() {
            const contenedor = document.getElementById('carrito-contenedor');
            const totalPrecio = document.getElementById('carrito-total-precio');
            
            contenedor.innerHTML = '';
            let total = 0;
            
            carrito.forEach((item, index) => {
                total += item.precio * item.cantidad;
                contenedor.innerHTML += `
                    <div class="carrito-item">
                        <div class="carrito-item-info">
                            <strong>${item.nombre}</strong><br>
                            $${item.precio.toFixed(2)} x ${item.cantidad}
                        </div>
                        <div style="text-align: right; margin-left: 10px;">
                            <div>$${(item.precio * item.cantidad).toFixed(2)}</div>
                            <button onclick="eliminarDelCarrito(${index})" style="color:white; background:#dc3545; border:none; cursor:pointer; padding: 2px 5px; border-radius: 3px; margin-top: 5px;">Eliminar</button>
                        </div>
                    </div>
                `;
            });
            
            totalPrecio.innerText = total.toFixed(2);
            localStorage.setItem('carrito', JSON.stringify(carrito));
        }

        function agregarAlCarrito(id, nombre, precio) {
            const index = carrito.findIndex(item => item.id === id);
            if (index > -1) {
                carrito[index].cantidad += 1;
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1 });
            }
            renderizarCarrito();
        }

        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            renderizarCarrito();
        }

        function procesarPago() {
            if (carrito.length === 0) {
                alert("Tu carrito está vacío.");
                return;
            }
            
            if (!isLoggedIn) {
                // Redirigir al login y luego regresar
                window.location.href = 'login.php?redirect=clienteTienda.php';
            } else {
                // Preparar datos y enviar al servidor
                document.getElementById('carrito-input').value = JSON.stringify(carrito);
                document.getElementById('form-pago').submit();
            }
        }

        // Renderizar el carrito al cargar la página
        window.onload = function() {
            renderizarCarrito();
        };
    </script>
</body>
</html>