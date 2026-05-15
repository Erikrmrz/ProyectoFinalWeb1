<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Dulcería Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <img src="../assets/img/logo_cinepolis.png" alt="Logo Dulcería" style="max-width: 200px;">
        <h2>Bienvenido a la Dulcería</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <p class="error-msg">Usuario o contraseña incorrectos.</p>
        <?php endif; ?>

        <form action="../controllers/LoginController.php" method="POST">
            <?php if(isset($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
            <?php endif; ?>
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>