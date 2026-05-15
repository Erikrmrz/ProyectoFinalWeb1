<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php"); exit();
}
require_once '../config/database.php';

$productos = $conexion->query("SELECT * FROM productos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando, cargar el producto
$editando = null;
if (isset($_GET['editar'])) {
    $stmtEdit = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmtEdit->execute([(int)$_GET['editar']]);
    $editando = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Productos | Cinépolis</title>
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

<!-- TABS DE ADMIN -->
<div class="admin-tabs">
    <a href="adminPanel.php" class="admin-tab active">🍿 Productos</a>
    <a href="adminPeliculas.php" class="admin-tab">🎬 Películas</a>
    <a href="adminHorarios.php" class="admin-tab">🕐 Horarios</a>
</div>

<div class="panel-content">
    <h2>Gestión de Productos</h2>
    <p class="desc">Administra los productos de la dulcería. Sube imágenes que se guardarán en la base de datos.</p>

    <?php if(isset($_GET['ok'])): ?>
        <div class="alert-success">✅ Operación realizada correctamente.</div>
    <?php endif; ?>

    <!-- FORMULARIO AGREGAR / EDITAR -->
    <div class="admin-form-card">
        <?php if ($editando): ?>
            <h3>✏️ Editando: <?= htmlspecialchars($editando['nombre']) ?></h3>
            <form action="../controllers/AdminProductosController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" value="<?= $editando['id'] ?>">
                <div class="form-row">
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="<?= htmlspecialchars($editando['nombre']) ?>" required></div>
                    <div class="form-group"><label>Precio ($)</label><input type="number" step="0.01" name="precio" value="<?= $editando['precio'] ?>" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="stock" value="<?= $editando['stock'] ?>" required></div>
                </div>
                <div class="form-group">
                    <label>Imagen (dejar vacío para conservar la actual)</label>
                    <input type="file" name="imagen" accept="image/*" class="file-input">
                </div>
                <div style="display:flex; gap:10px; margin-top:10px;">
                    <button type="submit" class="btn-pagar">💾 Guardar Cambios</button>
                    <a href="adminPanel.php" class="btn-vaciar" style="text-decoration:none; text-align:center; line-height:2.5;">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <h3>Agregar Producto</h3>
            <form action="../controllers/AdminProductosController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="agregar">
                <div class="form-row">
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" required></div>
                    <div class="form-group"><label>Precio ($)</label><input type="number" step="0.01" name="precio" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="stock" value="50" required></div>
                </div>
                <div class="form-group">
                    <label>Imagen</label>
                    <input type="file" name="imagen" accept="image/*" class="file-input">
                </div>
                <button type="submit" class="btn-pagar" style="margin-top:10px;">+ Agregar Producto</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <table class="data-table" style="margin-top:24px;">
        <thead>
            <tr><th>ID</th><th>Imagen</th><th>Producto</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $prod):
            $imgSrc = !empty($prod['imagen_blob'])
                ? '../controllers/ImagenController.php?tipo=producto&id=' . $prod['id']
                : '../assets/img/' . htmlspecialchars($prod['imagen'] ?? '');
        ?>
            <tr>
                <td><?= $prod['id'] ?></td>
                <td><img src="<?= $imgSrc ?>" width="48" height="48" style="object-fit:contain; border-radius:6px; background:var(--bg-primary);"
                         onerror="this.src='https://via.placeholder.com/48/1a365d/fbd304?text=🍿'"></td>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td>$<?= number_format($prod['precio'], 2) ?></td>
                <td><span class="badge-stock <?= $prod['stock'] > 5 ? 'ok' : 'low' ?>"><?= $prod['stock'] ?> uds.</span></td>
                <td class="actions-cell">
                    <a href="adminPanel.php?editar=<?= $prod['id'] ?>" class="btn-edit">Editar</a>
                    <a href="../controllers/AdminProductosController.php?accion=eliminar&id=<?= $prod['id'] ?>" class="btn-delete"
                       onclick="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($prod['nombre'])) ?>»?')">Eliminar</a>
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