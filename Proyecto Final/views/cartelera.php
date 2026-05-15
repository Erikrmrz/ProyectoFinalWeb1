<?php
session_start();
// Importamos la conexión de base de datos existente
require_once '../config/database.php';

$isLoggedIn = isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente';
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'Invitado';

// Consulta SQL dinámicamente uniendo películas y concatenando sus horarios
try {
    $query = "SELECT p.id, p.titulo, p.imagen, p.clasificacion, 
                     GROUP_CONCAT(TIME_FORMAT(h.hora, '%H:%i') ORDER BY h.hora SEPARATOR ',') as horarios_lista
              FROM peliculas p
              LEFT JOIN horarios h ON p.id = h.pelicula_id
              GROUP BY p.id 
              ORDER BY p.id ASC";
    $stmt = $conexion->query($query);
    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar la cartelera de la base de datos: " . $e->getMessage());
}
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
                <a href="login.php?redirect=cartelera.php" class="btn-header-login">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="cartelera-section">
        <h1>En Cartelera</h1>
        <div class="grid-peliculas">
            <?php foreach ($peliculas as $peli):
                // Convertimos la cadena de horarios separados por comas de la BD en un array de PHP
                $horarios = !empty($peli['horarios_lista']) ? explode(',', $peli['horarios_lista']) : [];
                ?>
                <div class="card-pelicula">
                    <img src="<?= htmlspecialchars($peli['imagen']) ?>" alt="<?= htmlspecialchars($peli['titulo']) ?>">
                    <div class="pelicula-info">
                        <h3>
                            <?= htmlspecialchars($peli['titulo']) ?>
                            <span class="badge-clasificacion"><?= htmlspecialchars($peli['clasificacion']) ?></span>
                        </h3>
                        <p class="horarios-titulo">Horarios disponibles:</p>
                        <div class="horarios-container">
                            <?php if (count($horarios) > 0): ?>
                                <?php foreach ($horarios as $hora): ?>
                                    <button class="horario-btn"><?= htmlspecialchars($hora) ?></button>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">Sin funciones hoy</p>
                            <?php endif; ?>
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