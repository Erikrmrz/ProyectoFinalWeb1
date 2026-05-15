<?php
session_start();
$isLoggedIn = isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente';
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'Invitado';

// Simulamos una base de datos de películas (Puedes pasarlo a MySQL después)
$peliculas = [
    ["titulo" => "Deadpool & Wolverine", "imagen" => "https://via.placeholder.com/250x350/1a365d/fff?text=Deadpool", "clasificacion" => "C", "horarios" => ["14:30", "17:00", "19:45", "22:15"]],
    ["titulo" => "Intensa-Mente 2", "imagen" => "https://via.placeholder.com/250x350/1a365d/fff?text=Intensa-Mente+2", "clasificacion" => "AA", "horarios" => ["13:00", "15:20", "18:10"]],
    ["titulo" => "Mi Villano Favorito 4", "imagen" => "https://via.placeholder.com/250x350/1a365d/fff?text=Villano+Fav+4", "clasificacion" => "A", "horarios" => ["12:45", "16:15", "18:40"]],
    ["titulo" => "Un Lugar en Silencio: Día Uno", "imagen" => "https://via.placeholder.com/250x350/1a365d/fff?text=Lugar+Silencio", "clasificacion" => "B15", "horarios" => ["20:00", "22:30"]],
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartelera - Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="app-header cine-navbar">
        <div class="nav-left">
            <a href="cartelera.php" class="logo-cinepolis">cinépolis</a>
            <nav class="main-menu">
                <a href="cartelera.php" class="menu-link active">Películas</a>
                <a href="clienteTienda.php" class="menu-link">Alimentos</a>
            </nav>
        </div>

        <div class="header-actions">
            <span class="header-user">Hola,
                <?= $username ?>
            </span>
            <div class="theme-switch-wrapper">
                <label class="theme-switch" title="Cambiar tema">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                </label>
            </div>
            <?php if ($isLoggedIn): ?>
                <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php?redirect=cartelera.php" class="btn-header-login">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="cartelera-section">
        <h1>En Cartelera</h1>
        <div class="grid-peliculas">
            <?php foreach ($peliculas as $peli): ?>
                <div class="card-pelicula">
                    <img src="<?= $peli['imagen'] ?>" alt="<?= htmlspecialchars($peli['titulo']) ?>">
                    <div class="pelicula-info">
                        <h3>
                            <?= htmlspecialchars($peli['titulo']) ?> <span class="badge-clasificacion">
                                <?= $peli['clasificacion'] ?>
                            </span>
                        </h3>
                        <p class="horarios-titulo">Horarios disponibles:</p>
                        <div class="horarios-container">
                            <?php foreach ($peli['horarios'] as $hora): ?>
                                <button class="horario-btn">
                                    <?= $hora ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        const toggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const saved = localStorage.getItem('theme') || 'dark';
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