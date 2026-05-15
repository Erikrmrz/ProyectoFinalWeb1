<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config/database.php';

    $user_input = trim($_POST['username']);
    $pass_input = trim($_POST['password']);
    $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';
    $login_success = false;

    // Consultar la base de datos de usuarios
    $stmt = $conexion->prepare("SELECT id, username, password, rol FROM usuarios WHERE username = ?");
    $stmt->execute([$user_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] === $pass_input) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        $login_success = true;
    }

    if ($login_success) {
        // Redirección basada en roles y parámetro redirect
        if ($_SESSION['rol'] == 'administrador') {
            header("Location: ../views/adminPanel.php");
        } elseif ($_SESSION['rol'] == 'empleado') {
            header("Location: ../views/empleadoPanel.php");
        } else {
            // Si hay un redireccionamiento pendiente y es un cliente
            if (!empty($redirect)) {
                header("Location: ../views/" . $redirect);
            } else {
                header("Location: ../views/clienteTienda.php");
            }
        }
        exit();
    } else {
        // Manejo básico de errores
        $error_url = "../views/login.php?error=1";
        if (!empty($redirect)) {
            $error_url .= "&redirect=" . urlencode($redirect);
        }
        header("Location: " . $error_url);
        exit();
    }
}
?>