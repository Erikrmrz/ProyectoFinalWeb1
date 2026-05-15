<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>

    <!-- Switch de tema flotante -->
    <div class="login-theme-wrapper">
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="login-page">
        <div class="login-container">
            <img src="../assets/img/logo_cinepolis.png" alt="Logo Cinépolis" class="logo-login"
                 onerror="this.style.display='none'">
            <h2>Crear Cuenta</h2>
            <p class="subtitle">Regístrate para comprar boletos y alimentos</p>

            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">❌ <?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <form action="../controllers/RegistroController.php" method="POST">
                <?php if(isset($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Elige un nombre de usuario" required autofocus minlength="3">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 4 caracteres" required minlength="4">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required minlength="4">
                </div>

                <button type="submit" class="btn-login">Crear Cuenta →</button>
            </form>

            <div class="login-footer">
                <a href="login.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>">← Ya tengo cuenta, iniciar sesión</a>
            </div>
        </div>
    </div>

<script>
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
</script>

</body>
</html>
