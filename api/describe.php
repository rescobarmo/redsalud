<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();
    $tabla = $_GET['tabla'] ?? 'redsalud';
    $cols = $pdo->query("DESCRIBE `$tabla`")->fetchAll();
    $data = $pdo->query("SELECT * FROM `$tabla` ORDER BY 1 DESC LIMIT 20")->fetchAll();
    echo json_encode([
        'columnas' => $cols,
        'datos' => $data,
        'total' => $pdo->query("SELECT COUNT(*) as c FROM `$tabla`")->fetch()['c']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
