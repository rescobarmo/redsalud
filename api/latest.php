<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();
    $desde = $_GET['desde'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));

    $stmt = $pdo->prepare("SELECT * FROM redsalud WHERE fecha_creacion > ? ORDER BY fecha_creacion ASC");
    $stmt->execute([$desde]);
    $registros = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'total' => count($registros),
        'ultimo' => $registros ? end($registros)['fecha_creacion'] : $desde,
        'data' => $registros
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
