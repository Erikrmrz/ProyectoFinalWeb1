<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);
    $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';

    // Validaciones
    if (empty($username) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (strlen($username) < 3) {
        $error = "El usuario debe tener al menos 3 caracteres.";
    } elseif (strlen($password) < 4) {
        $error = "La contraseña debe tener al menos 4 caracteres.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Ese nombre de usuario ya está en uso.";
        } else {
            // Insertar nuevo cliente
            $stmt = $conexion->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, 'cliente')");
            $stmt->execute([$username, $password]);

            // Auto-login después del registro
            $_SESSION['user_id']  = $conexion->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['rol']      = 'cliente';

            // Redirigir
            if (!empty($redirect)) {
                header("Location: ../views/" . $redirect);
            } else {
                header("Location: ../views/cartelera.php");
            }
            exit();
        }
    }

    // Si hubo error, redirigir con mensaje
    $error_url = "../views/registro.php?error=" . urlencode($error);
    if (!empty($redirect)) {
        $error_url .= "&redirect=" . urlencode($redirect);
    }
    header("Location: " . $error_url);
    exit();
}
?>
