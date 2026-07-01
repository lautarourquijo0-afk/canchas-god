<?php
/* Crea o edita un turno. Solo admin y recepción. */

require __DIR__ . '/../auth.php';
require __DIR__ . '/../conexion.php';
requerir_rol(['admin', 'recepcion']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

$id      = $_POST['id'] ?? '';
$sede_id = (int)($_POST['sede_id'] ?? 0);
$fecha   = $_POST['fecha'] ?? date('Y-m-d');
$inicio  = $_POST['hora_inicio'] ?? '';
$fin     = $_POST['hora_fin'] ?? '';
$cancha  = trim($_POST['cancha'] ?? 'Cancha 1');
$cliente = trim($_POST['cliente'] ?? '');
$estado  = (($_POST['estado'] ?? 'reservado') === 'libre') ? 'libre' : 'reservado';

// Si está libre, no guardamos nombre de cliente.
if ($estado === 'libre') {
    $cliente = null;
} elseif ($cliente === '') {
    $cliente = 'Reservado';
}

if (!$sede_id || !$inicio || !$fin || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(400);
    die('Faltan datos obligatorios.');
}

if ($id !== '' && ctype_digit((string)$id)) {
    $stmt = $pdo->prepare(
        "UPDATE turnos SET sede_id=?, fecha=?, hora_inicio=?, hora_fin=?, cancha=?, cliente=?, estado=?
         WHERE id=?"
    );
    $stmt->execute([$sede_id, $fecha, $inicio, $fin, $cancha, $cliente, $estado, (int)$id]);
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO turnos (sede_id, fecha, hora_inicio, hora_fin, cancha, cliente, estado)
         VALUES (?,?,?,?,?,?,?)"
    );
    $stmt->execute([$sede_id, $fecha, $inicio, $fin, $cancha, $cliente, $estado]);
}

header('Location: /admin/index.php?fecha=' . urlencode($fecha) . '&ok=1');
