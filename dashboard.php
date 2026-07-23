<?php
require_once __DIR__ . '/includes/auth.php';
requerirLogin();

$pdo = getDB();
$usuario = usuarioActual();

try {
$stats = $pdo->query("
    SELECT
        COUNT(*) as total_msgs,
        COUNT(DISTINCT numero) as contactos_unicos,
        COUNT(DISTINCT CASE WHEN DATE(fecha_creacion) = CURDATE() THEN id END) as msgs_hoy,
        COUNT(DISTINCT CASE WHEN categoria_cliente = 'cotizando' OR categoria_cliente = 'COTIZANDO' THEN id END) as cotizando,
        COUNT(DISTINCT CASE WHEN categoria_cliente = 'respondio' OR categoria_cliente = 'RESPONDIO' THEN id END) as respondio,
        COUNT(DISTINCT CASE WHEN categoria_cliente = 'realizado' OR categoria_cliente = 'REALIZADO' THEN id END) as realizado
    FROM redsalud
")->fetch();
} catch (Exception $e) { die("Error query 1: " . $e->getMessage()); }

try {
$msgsPorDia = $pdo->query("
    SELECT
        DATE_FORMAT(fecha_creacion, '%d/%m') as dia,
        COUNT(*) as total
    FROM redsalud
    WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(fecha_creacion)
    ORDER BY DATE(fecha_creacion) ASC
")->fetchAll();
} catch (Exception $e) { die("Error query 2: " . $e->getMessage()); }

try {
$categorias = $pdo->query("
    SELECT
        CASE
            WHEN LOWER(categoria_cliente) = 'cotizando' THEN 'COTIZANDO'
            WHEN LOWER(categoria_cliente) = 'respondio' THEN 'RESPONDIO'
            WHEN LOWER(categoria_cliente) = 'realizado' THEN 'REALIZADO'
            ELSE 'SIN CATEGORÍA'
        END as cat,
        COUNT(*) as total
    FROM redsalud
    GROUP BY cat
    ORDER BY total DESC
")->fetchAll();
} catch (Exception $e) { die("Error query 3: " . $e->getMessage()); }

try {
$ultimosContactos = $pdo->query("
    SELECT numero, nombre, MAX(fecha_creacion) as ultimo
    FROM redsalud
    GROUP BY numero
    ORDER BY ultimo DESC
    LIMIT 10
")->fetchAll();
} catch (Exception $e) { die("Error query 4: " . $e->getMessage()); }

$tieneDatos = $stats['total_msgs'] > 0;
?>
<?php $titulo = 'Dashboard'; include __DIR__ . '/includes/header.php'; ?>

<div class="flex min-h-screen">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 ml-64 p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard Red Salud</h1>
                    <p class="text-slate-500 mt-1">Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                </div>
            </div>

            <?php if (!$tieneDatos): ?>
            <div class="flex flex-col items-center justify-center h-96 text-slate-400">
                <i class="fas fa-comments text-6xl mb-4 opacity-30"></i>
                <p class="text-xl font-medium">No hay conversaciones aún</p>
                <p class="text-sm">Los datos aparecerán cuando se registren mensajes</p>
            </div>
            <?php else: ?>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8 fade-in">
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Mensajes</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-comments text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= number_format($stats['total_msgs']) ?></p>
                    <p class="text-xs text-slate-400 mt-1">acumulados</p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Contactos</span>
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-green-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= number_format($stats['contactos_unicos']) ?></p>
                    <p class="text-xs text-slate-400 mt-1">números únicos</p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">COTIZANDO</span>
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-invoice text-red-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= $stats['cotizando'] ?></p>
                    <p class="text-xs text-slate-400 mt-1">en proceso</p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">RESPONDIO</span>
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-reply text-green-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= $stats['respondio'] ?></p>
                    <p class="text-xs text-slate-400 mt-1">respondieron</p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">REALIZADO</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= $stats['realizado'] ?></p>
                    <p class="text-xs text-slate-400 mt-1">completados</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 fade-in">
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-slate-800">Mensajes por Día</h3>
                        <span class="text-xs text-slate-400">Últimos 14 días</span>
                    </div>
                    <?php if (!empty($msgsPorDia)): ?>
                    <canvas id="chartMsgs" height="280"></canvas>
                    <?php else: ?>
                    <div class="h-64 flex items-center justify-center text-slate-400">
                        <p>Sin datos en los últimos 14 días</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-slate-800">Categorías</h3>
                        <span class="text-xs text-slate-400">distribución</span>
                    </div>
                    <?php if (!empty($categorias)): ?>
                    <canvas id="chartCategorias" height="280"></canvas>
                    <?php else: ?>
                    <div class="h-64 flex items-center justify-center text-slate-400">
                        <p>Sin categorías asignadas</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-100 fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-slate-800">Últimos Contactos</h3>
                    <a href="<?= APP_URL ?>/pages/campanas.php" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Ver todos →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 text-xs uppercase tracking-wider">
                                <th class="pb-3 font-medium">Contacto</th>
                                <th class="pb-3 font-medium">Teléfono</th>
                                <th class="pb-3 font-medium">Último mensaje</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($ultimosContactos as $c): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 font-medium text-slate-800"><?= htmlspecialchars($c['nombre'] ?? 'Sin nombre') ?></td>
                                <td class="py-3 font-mono text-slate-600"><?= htmlspecialchars($c['numero']) ?></td>
                                <td class="py-3 text-slate-500"><?= date('d/m/Y H:i', strtotime($c['ultimo'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </main>
</div>

<?php if ($tieneDatos): ?>
<script>
<?php if (!empty($msgsPorDia)): ?>
new Chart(document.getElementById('chartMsgs'), {
    type: 'bar',
    data: {
        labels: [<?php foreach ($msgsPorDia as $m): ?>'<?= $m['dia'] ?>',<?php endforeach; ?>],
        datasets: [{
            label: 'Mensajes',
            data: [<?php foreach ($msgsPorDia as $m): ?><?= $m['total'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        },
        animation: { duration: 600, easing: 'easeOutQuart' }
    }
});
<?php endif; ?>

<?php if (!empty($categorias)): ?>
new Chart(document.getElementById('chartCategorias'), {
    type: 'doughnut',
    data: {
        labels: [<?php foreach ($categorias as $c): ?>'<?= $c['cat'] ?>',<?php endforeach; ?>],
        datasets: [{
            data: [<?php foreach ($categorias as $c): ?><?= $c['total'] ?>,<?php endforeach; ?>],
            backgroundColor: ['#ef4444', '#22c55e', '#3b82f6', '#94a3b8'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12 } }
        },
        cutout: '65%'
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
