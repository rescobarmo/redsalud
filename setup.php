<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Setup Dashboard</title>";
echo "<script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-900 text-white p-8'>";
echo "<div class='max-w-2xl mx-auto'><h1 class='text-2xl font-bold mb-6'>⚙️ Configuración del Dashboard</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;port=3306;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    if (!$sql) throw new Exception('No se pudo leer el archivo schema.sql');

    $statements = explode(';', $sql);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                echo "<div class='bg-yellow-900/50 border border-yellow-700 rounded p-3 mb-2 text-sm'>⚠️ " .
                     htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }

    echo "<div class='bg-green-900/50 border border-green-700 rounded p-4 mb-4 text-green-300'>";
    echo "✅ Base de datos configurada correctamente</div>";

    $tables = $pdo->query("SHOW TABLES FROM marketing")->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='bg-slate-800 rounded p-4'><h2 class='font-semibold mb-3'>Tablas creadas:</h2><ul class='space-y-1'>";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) as c FROM marketing.$table")->fetch();
        echo "<li class='flex items-center gap-2 text-slate-300'>";
        echo "<span class='text-green-400'>✓</span> {$table} <span class='text-slate-500 text-sm'>({$count['c']} registros)</span></li>";
    }
    echo "</ul></div>";

    echo "<div class='mt-6 bg-blue-900/50 border border-blue-700 rounded p-4 text-blue-300'>";
    echo "🔑 Credenciales de acceso:<br>";
    echo "📧 admin@redsalud.cl / password (Administrador)<br>";
    echo "📧 editor@redsalud.cl / password (Editor)";
    echo "</div>";

    echo "<div class='mt-4 flex gap-3'>";
    echo "<a href='index.php' class='px-6 py-2 bg-blue-600 rounded-lg hover:bg-blue-700 transition'>Ir al Login</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='bg-red-900/50 border border-red-700 rounded p-4 text-red-300'>";
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div></body></html>";
