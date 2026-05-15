<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php"); exit();
}
require_once '../config/database.php';

$peliculas = $conexion->query("SELECT * FROM peliculas ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando, cargar la película
$editando = null;
if (isset($_GET['editar'])) {
    $stmtEdit = $conexion->prepare("SELECT * FROM peliculas WHERE id = ?");
    $stmtEdit->execute([(int)$_GET['editar']]);
    $editando = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}

$clasificaciones = ['AA', 'A', 'B', 'B15', 'C', 'D'];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Películas | Cinépolis</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="panel-layout">

<nav class="panel-nav">
    <span class="panel-nav-title">Panel: <strong>Administrador</strong> — <?= htmlspecialchars($_SESSION['username']) ?></span>
    <div class="panel-nav-actions">
        <div class="theme-switch-wrapper">
            <label class="theme-switch" title="Cambiar tema"><input type="checkbox" id="themeToggle"><span class="slider"></span></label>
        </div>
        <a href="../controllers/LogoutController.php" class="btn-header-logout">Cerrar Sesión</a>
    </div>
</nav>

<div class="admin-tabs">
    <a href="adminPanel.php" class="admin-tab">🍿 Productos</a>
    <a href="adminPeliculas.php" class="admin-tab active">🎬 Películas</a>
    <a href="adminHorarios.php" class="admin-tab">🕐 Horarios</a>
</div>

<div class="panel-content">
    <h2>Gestión de Películas</h2>
    <p class="desc">Administra la cartelera de películas. Las imágenes se almacenan en la base de datos como BLOB.</p>

    <?php if(isset($_GET['ok'])): ?>
        <div class="alert-success">✅ Operación realizada correctamente.</div>
    <?php endif; ?>

    <div class="admin-form-card">
        <?php if ($editando): ?>
            <h3>✏️ Editando: <?= htmlspecialchars($editando['titulo']) ?></h3>
            <form action="../controllers/AdminPeliculasController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" value="<?= $editando['id'] ?>">
                <div class="form-row">
                    <div class="form-group" style="flex:2"><label>Título</label><input type="text" name="titulo" value="<?= htmlspecialchars($editando['titulo']) ?>" required></div>
                    <div class="form-group"><label>Clasificación</label>
                        <select name="clasificacion" required>
                            <?php foreach ($clasificaciones as $c): ?>
                                <option value="<?= $c ?>" <?= $editando['clasificacion'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Imagen / Póster (dejar vacío para conservar el actual)</label>
                    <input type="file" name="imagen" accept="image/*" class="file-input">
                </div>
                <div style="display:flex; gap:10px; margin-top:10px;">
                    <button type="submit" class="btn-pagar">💾 Guardar Cambios</button>
                    <a href="adminPeliculas.php" class="btn-vaciar" style="text-decoration:none; text-align:center; line-height:2.5;">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <h3>Agregar Película</h3>
            <form action="../controllers/AdminPeliculasController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="agregar">
                <div class="form-row">
                    <div class="form-group" style="flex:2"><label>Título</label><input type="text" name="titulo" required></div>
                    <div class="form-group"><label>Clasificación</label>
                        <select name="clasificacion" required>
                            <?php foreach ($clasificaciones as $c): ?>
                                <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Imagen / Póster</label>
                    <input type="file" name="imagen" accept="image/*" class="file-input" required>
                </div>
                <button type="submit" class="btn-pagar" style="margin-top:10px;">+ Agregar Película</button>
            </form>
        <?php endif; ?>
    </div>

    <table class="data-table" style="margin-top:24px;">
        <thead>
            <tr><th>ID</th><th>Póster</th><th>Título</th><th>Clasificación</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($peliculas as $peli):
            $imgSrc = !empty($peli['imagen_blob'])
                ? '../controllers/ImagenController.php?tipo=pelicula&id=' . $peli['id']
                : '../assets/img/' . htmlspecialchars($peli['imagen'] ?? '');
        ?>
            <tr>
                <td><?= $peli['id'] ?></td>
                <td><img src="<?= $imgSrc ?>" width="40" height="56" style="object-fit:cover; border-radius:4px;"
                         onerror="this.src='https://via.placeholder.com/40x56/1a365d/fbd304?text=🎬'"></td>
                <td><?= htmlspecialchars($peli['titulo']) ?></td>
                <td><span class="badge-clasificacion"><?= htmlspecialchars($peli['clasificacion']) ?></span></td>
                <td class="actions-cell">
                    <a href="adminPeliculas.php?editar=<?= $peli['id'] ?>" class="btn-edit">Editar</a>
                    <a href="../controllers/AdminPeliculasController.php?accion=eliminar&id=<?= $peli['id'] ?>" class="btn-delete"
                       onclick="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($peli['titulo'])) ?>» y todos sus horarios?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const toggle = document.getElementById('themeToggle');
const html = document.documentElement;
const saved = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', saved);
toggle.checked = (saved === 'light');
toggle.addEventListener('change', () => { const t = toggle.checked ? 'light' : 'dark'; html.setAttribute('data-theme', t); localStorage.setItem('theme', t); });
</script>
</body>
</html>
