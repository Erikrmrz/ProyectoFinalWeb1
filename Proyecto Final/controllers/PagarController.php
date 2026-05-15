<?php
session_start();
require_once '../config/database.php';

// Validar que el usuario esté logueado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente' || !isset($_SESSION['user_id'])) {
    // Si por alguna razón llegó aquí sin sesión, lo mandamos al login
    header("Location: ../views/login.php?redirect=clienteTienda.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['carrito'])) {
    $carrito = json_decode($_POST['carrito'], true);

    if (is_array($carrito) && count($carrito) > 0) {
        try {
            // Iniciar transacción
            $conexion->beginTransaction();

            $usuario_id = $_SESSION['user_id'];
            $total = 0;

            // Calcular total real para seguridad y no depender solo del JS
            foreach ($carrito as $item) {
                // Verificar que el producto exista y obtener su precio real
                $stmt = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
                $stmt->execute([$item['id']]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($producto) {
                    $total += $producto['precio'] * $item['cantidad'];
                }
            }

            // 1. Crear el registro de la venta
            $stmtVenta = $conexion->prepare("INSERT INTO ventas (usuario_id, total) VALUES (?, ?)");
            $stmtVenta->execute([$usuario_id, $total]);
            $venta_id = $conexion->lastInsertId();

            // 2. Insertar los detalles de la venta y actualizar stock
            $stmtDetalle = $conexion->prepare("INSERT INTO ventas_detalles (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmtStock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

            foreach ($carrito as $item) {
                // Buscamos el precio real de nuevo (o podríamos haberlo guardado)
                $stmtPrecio = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
                $stmtPrecio->execute([$item['id']]);
                $productoReal = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

                if ($productoReal) {
                    // Insertar detalle
                    $stmtDetalle->execute([
                        $venta_id,
                        $item['id'],
                        $item['cantidad'],
                        $productoReal['precio']
                    ]);

                    // Actualizar stock
                    $stmtStock->execute([$item['cantidad'], $item['id']]);
                }
            }

            // Confirmar transacción
            $conexion->commit();

            // Redirigir a la tienda con mensaje de éxito
            header("Location: ../views/clienteTienda.php?compra=exitosa");
            exit();

        } catch (Exception $e) {
            // Revertir en caso de error
            $conexion->rollBack();
            die("Ocurrió un error al procesar tu pago: " . $e->getMessage());
        }
    } else {
        // Carrito vacío
        header("Location: ../views/clienteTienda.php");
        exit();
    }
} else {
    // Acceso no permitido (GET, etc.)
    header("Location: ../views/clienteTienda.php");
    exit();
}
?>