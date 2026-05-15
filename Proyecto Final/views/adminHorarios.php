<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php"); exit();
}
require_once '../config/database.php';

// Horarios con nombre de película
$horarios = $conexion->query(
    "SELECT h.*, p.titulo FROM horarios h JOIN peliculas p ON h.pelicula_id = p.id ORDER BY p.titulo, h.hora"
)->fetchAll(PDO::FETCH_ASSOC);

// Lista de películas para el select
$peliculas = $conexion->query("SELECT id, titulo FROM peliculas ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando, cargar el horario
$editando = null;
if (isset($_GET['editar'])) {
    $stmtEdit = $conexion->prepare("SELECT * FROM horarios WHERE id = ?");
    $stmtEdit->execute([(int)$_GET['editar']]);
    $editando = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}

$salas = [
    'Sala 1' => 60, 'Sala 2' => 55, 'Sala 3' => 50,
    'Sala 4' => 45, 'Sala 5' => 40
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Horarios | Cinépolis</title>
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
    <a href="adminPeliculas.php" class="admin-tab">🎬 Películas</a>
    <a href="adminHorarios.php" class="admin-tab active">🕐 Horarios</a>
</div>

<div class="panel-content">
    <h2>Gestión de Horarios</h2>
    <p class="desc">Asigna funciones a las películas con sala, horario y precio.</p>

    <?php if(isset($_GET['ok'])): ?>
        <div class="alert-success">✅ Operación realizada correctamente.</div>
    <?php endif; ?>

    <div class="admin-form-card">
        <?php if ($editando): ?>
            <h3>✏️ Editando Horario #<?= $editando['id'] ?></h3>
            <form action="../controllers/AdminHorariosController.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" value="<?= $editando['id'] ?>">
                <div class="form-row">
                    <div class="form-group" style="flex:2">
                        <label>Película</label>
                        <select name="pelicula_id" required>
                            <?php foreach ($peliculas as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $editando['pelicula_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <select name="hora" required>
                            <?php
                            $editHora = date('H:i', strtotime($editando['hora']));
                            for($h=10; $h<=23; $h++) {
                                foreach(['00', '15', '30', '45'] as $m) {
                                    $timeVal = sprintf("%02d:%s", $h, $m);
                                    $sel = ($timeVal === $editHora) ? 'selected' : '';
                                    echo "<option value=\"$timeVal\" $sel>$timeVal hrs</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Precio ($)</label><input type="number" step="0.01" name="precio" value="<?= $editando['precio'] ?>" required></div>
                    <div class="form-group">
                        <label>Sala</label>
                        <select name="sala" id="sala-select" onchange="document.getElementById('asientos-input').value = this.options[this.selectedIndex].dataset.asientos" required>
                            <?php foreach ($salas as $nombre => $asientos): ?>
                                <option value="<?= $nombre ?>" data-asientos="<?= $asientos ?>" <?= $editando['sala'] === $nombre ? 'selected' : '' ?>><?= $nombre ?> (<?= $asientos ?> lugares)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Asientos Totales</label><input type="number" name="asientos_totales" id="asientos-input" value="<?= $editando['asientos_totales'] ?>" readonly style="background:var(--bg-secondary); cursor:not-allowed;"></div>
                </div>
                <div style="display:flex; gap:10px; margin-top:10px;">
                    <button type="submit" class="btn-pagar">💾 Guardar Cambios</button>
                    <a href="adminHorarios.php" class="btn-vaciar" style="text-decoration:none; text-align:center; line-height:2.5;">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <h3>Agregar Horario</h3>
            <form action="../controllers/AdminHorariosController.php" method="POST">
                <input type="hidden" name="accion" value="agregar">
                <div class="form-row">
                    <div class="form-group" style="flex:2">
                        <label>Película</label>
                        <select name="pelicula_id" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($peliculas as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <select name="hora" required>
                            <option value="">— Seleccionar —</option>
                            <?php
                            for($h=10; $h<=23; $h++) {
                                foreach(['00', '15', '30', '45'] as $m) {
                                    $timeVal = sprintf("%02d:%s", $h, $m);
                                    echo "<option value=\"$timeVal\">$timeVal hrs</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Precio ($)</label><input type="number" step="0.01" name="precio" value="70.00" required></div>
                    <div class="form-group">
                        <label>Sala</label>
                        <select name="sala" id="sala-select" onchange="document.getElementById('asientos-input').value = this.options[this.selectedIndex].dataset.asientos" required>
                            <?php foreach ($salas as $nombre => $asientos): ?>
                                <option value="<?= $nombre ?>" data-asientos="<?= $asientos ?>"><?= $nombre ?> (<?= $asientos ?> lugares)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Asientos Totales</label><input type="number" name="asientos_totales" id="asientos-input" value="60" readonly style="background:var(--bg-secondary); cursor:not-allowed;"></div>
                </div>
                <button type="submit" class="btn-pagar" style="margin-top:10px;">+ Agregar Horario</button>
            </form>
        <?php endif; ?>
    </div>

    <table class="data-table" style="margin-top:24px;">
        <thead>
            <tr><th>ID</th><th>Película</th><th>Hora</th><th>Precio</th><th>Sala</th><th>Asientos</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($horarios as $h): ?>
            <tr>
                <td><?= $h['id'] ?></td>
                <td><?= htmlspecialchars($h['titulo']) ?></td>
                <td><?= date('H:i', strtotime($h['hora'])) ?></td>
                <td>$<?= number_format($h['precio'], 2) ?></td>
                <td><?= htmlspecialchars($h['sala']) ?></td>
                <td><?= $h['asientos_totales'] ?></td>
                <td class="actions-cell">
                    <a href="adminHorarios.php?editar=<?= $h['id'] ?>" class="btn-edit">Editar</a>
                    <a href="../controllers/AdminHorariosController.php?accion=eliminar&id=<?= $h['id'] ?>" class="btn-delete"
                       onclick="return confirm('¿Eliminar este horario?')">Eliminar</a>
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
