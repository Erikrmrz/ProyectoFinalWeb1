<?php
session_start();
require_once '../config/database.php';

// Validar sesión
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../views/login.php?redirect=cartelera.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['horario_id']) || !isset($_POST['asientos'])) {
    header("Location: ../views/cartelera.php");
    exit();
}

$horario_id = (int)$_POST['horario_id'];
$asientos   = json_decode($_POST['asientos'], true);

if (!is_array($asientos) || count($asientos) === 0) {
    header("Location: ../views/seleccionAsientos.php?horario_id=$horario_id&error=sin_asientos");
    exit();
}

try {
    $conexion->beginTransaction();

    // Obtener info del horario
    $stmt = $conexion->prepare("SELECT h.*, p.titulo FROM horarios h JOIN peliculas p ON h.pelicula_id = p.id WHERE h.id = ?");
    $stmt->execute([$horario_id]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        throw new Exception("Horario no encontrado.");
    }

    // Verificar que los asientos no estén ocupados
    foreach ($asientos as $asiento) {
        $stmtCheck = $conexion->prepare(
            "SELECT COUNT(*) FROM boletos_asientos ba 
             JOIN boletos b ON ba.boleto_id = b.id 
             WHERE b.horario_id = ? AND ba.fila = ? AND ba.numero = ?"
        );
        $stmtCheck->execute([$horario_id, $asiento['fila'], $asiento['numero']]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("El asiento " . $asiento['fila'] . $asiento['numero'] . " ya está ocupado.");
        }
    }

    // Calcular total
    $total = $horario['precio'] * count($asientos);

    // Generar datos del QR
    $asientos_str = implode(', ', array_map(function($a) { return $a['fila'] . $a['numero']; }, $asientos));
    $qr_info = json_encode([
        'boleto'   => 'CINE-' . time(),
        'pelicula' => $horario['titulo'],
        'hora'     => date('H:i', strtotime($horario['hora'])),
        'sala'     => $horario['sala'],
        'asientos' => $asientos_str,
        'total'    => '$' . number_format($total, 2)
    ], JSON_UNESCAPED_UNICODE);

    // Insertar boleto
    $stmtBoleto = $conexion->prepare("INSERT INTO boletos (usuario_id, horario_id, total, qr_data) VALUES (?, ?, ?, ?)");
    $stmtBoleto->execute([$_SESSION['user_id'], $horario_id, $total, $qr_info]);
    $boleto_id = $conexion->lastInsertId();

    // Insertar asientos
    $stmtAsiento = $conexion->prepare("INSERT INTO boletos_asientos (boleto_id, fila, numero) VALUES (?, ?, ?)");
    foreach ($asientos as $asiento) {
        $stmtAsiento->execute([$boleto_id, $asiento['fila'], $asiento['numero']]);
    }

    $conexion->commit();

    header("Location: ../views/confirmarBoleto.php?boleto_id=$boleto_id");
    exit();

} catch (Exception $e) {
    $conexion->rollBack();
    $error = urlencode($e->getMessage());
    header("Location: ../views/seleccionAsientos.php?horario_id=$horario_id&error=$error");
    exit();
}
?>
