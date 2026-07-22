<?php
define('DB_HOST', getenv('DB_HOST') ?: 'rsr-srv-rsr-phpmyadmin.jdseuk.easypanel.host');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'marketing');
define('DB_USER', getenv('DB_USER') ?: 'rsr');
define('DB_PASS', getenv('DB_PASS') ?: 'N@v3gador');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    try {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST, (int)DB_PORT, DB_NAME, DB_CHARSET
            ),
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode([
            'error' => true,
            'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]));
    }
}
