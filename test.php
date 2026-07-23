<?php
require_once __DIR__ . '/config/database.php';
echo "<h1>Test DB</h1>";
echo "<p>DB_HOST: " . DB_HOST . "</p>";
echo "<p>DB_USER: " . DB_USER . "</p>";
echo "<p>DB_NAME: " . DB_NAME . "</p>";
try {
    $pdo = getDB();
    echo "<p>Conexión OK</p>";
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM redsalud");
    $row = $stmt->fetch();
    echo "<p>redsalud rows: " . $row['c'] . "</p>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
