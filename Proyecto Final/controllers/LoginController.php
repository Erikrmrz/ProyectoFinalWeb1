<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST['username'];
    $pass_input = $_POST['password'];
    $login_success = false;

    // Cargar el archivo XML de usuarios
    $usuarios = simplexml_load_file('../models/usuarios.xml');

    foreach ($usuarios->usuario as $user) {
        if ($user->username == $user_input && $user->password == $pass_input) {
            $_SESSION['username'] = (string)$user->username;
            $_SESSION['rol'] = (string)$user->rol;
            $login_success = true;
            break;
        }
    }

    if ($login_success) {
        // Redirección basada en roles
        if ($_SESSION['rol'] == 'administrador') {
            header("Location: ../views/admin_dashboard.php");
        } elseif ($_SESSION['rol'] == 'empleado') {
            header("Location: ../views/empleado_dashboard.php");
        } else {
            header("Location: ../views/cliente_tienda.php");
        }
        exit();
    } else {
        // Manejo básico de errores
        header("Location: ../views/login.php?error=1");
        exit();
    }
}
?>