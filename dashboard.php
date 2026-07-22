<?php
require_once __DIR__ . '/includes/auth.php';
requerirLogin();

$pdo = getDB();
$usuario = usuarioActual();

$filtroPeriodo = $_GET['periodo'] ?? '30';
$dias = (int)$filtroPeriodo;

$where = $dias > 0 ? "WHERE mc.fecha >= DATE_SUB(CURDATE(), INTERVAL $dias DAY)" : '';

$statsGenerales = $pdo->query("
    SELECT
        COALESCE(SUM(mc.ingresos), 0) as ingresos_totales,
        COALESCE(SUM(mc.gasto), 0) as gasto_total,
        COALESCE(SUM(mc.impresiones), 0) as impresiones_totales,
        COALESCE(SUM(mc.clicks), 0) as clicks_totales,
        COALESCE(SUM(mc.conversiones), 0) as conversiones_totales,
        CASE WHEN SUM(mc.gasto) > 0
            THEN ROUND((SUM(mc.ingresos) - SUM(mc.gasto)) / SUM(mc.gasto) * 100, 1)
            ELSE 0 END as roi
    FROM metricas_campana mc
    $where
")->fetch();

$totalCampañas = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as activas FROM campanas")->fetch();
$totalLeads = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN estado = 'ganado' THEN 1 ELSE 0 END) as ganados FROM leads")->fetch();

$ingresosPorMes = $pdo->query("
    SELECT
        DATE_FORMAT(fecha, '%Y-%m') as mes,
        SUM(ingresos) as ingresos,
        SUM(gasto) as gasto
    FROM metricas_campana
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
")->fetchAll();

$topCampañas = $pdo->query("
    SELECT c.nombre, c.estado, c.presupuesto, c.inversion,
           COALESCE(SUM(mc.ingresos), 0) as ingresos,
           COALESCE(SUM(mc.conversiones), 0) as conversiones,
           CASE WHEN SUM(mc.gasto) > 0
               THEN ROUND((SUM(mc.ingresos) - SUM(mc.gasto)) / SUM(mc.gasto) * 100, 1)
               ELSE 0 END as roi
    FROM campanas c
    LEFT JOIN metricas_campana mc ON c.id = mc.campana_id
    GROUP BY c.id
    ORDER BY ingresos DESC
    LIMIT 5
")->fetchAll();

$leadsPorEstado = $pdo->query("
    SELECT estado, COUNT(*) as total
    FROM leads
    GROUP BY estado
    ORDER BY total DESC
")->fetchAll();

$canalesRendimiento = $pdo->query("
    SELECT ch.nombre, ch.color, ch.icono,
           COALESCE(SUM(mc.impresiones), 0) as impresiones,
           COALESCE(SUM(mc.clicks), 0) as clicks,
           COALESCE(SUM(mc.conversiones), 0) as conversiones,
           COALESCE(SUM(mc.ingresos), 0) as ingresos,
           COALESCE(SUM(mc.gasto), 0) as gasto
    FROM canales ch
    LEFT JOIN campanas ca ON ch.id = ca.canal_id
    LEFT JOIN metricas_campana mc ON ca.id = mc.campana_id
    GROUP BY ch.id
    HAVING impresiones > 0 OR clicks > 0
    ORDER BY ingresos DESC
")->fetchAll();

$visitasRecientes = $pdo->query("
    SELECT DATE_FORMAT(fecha, '%d/%m') as dia, visitantes, visitas
    FROM visitas_sitio
    ORDER BY fecha DESC
    LIMIT 14
")->fetchAll();
$visitasRecientes = array_reverse($visitasRecientes);
?>
<?php $titulo = 'Dashboard'; include __DIR__ . '/includes/header.php'; ?>

<div class="flex min-h-screen">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 ml-64 p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-slate-500 mt-1">Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-500"><i class="far fa-calendar mr-1"></i>Período:</span>
                    <div class="flex bg-white rounded-lg border border-slate-200 p-0.5 shadow-sm">
                        <?php foreach ([7 => '7d', 15 => '15d', 30 => '30d', 90 => '90d', 0 => 'Todo'] as $val => $label): ?>
                            <a href="?periodo=<?= $val ?>"
                               class="filter-btn px-3 py-1.5 text-xs font-medium rounded-md transition-all <?= $dias === $val ? 'active' : 'text-slate-600 hover:text-slate-800' ?>">
                                <?= $label ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 fade-in">
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ingresos</span>
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">$<?= number_format($statsGenerales['ingresos_totales'], 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                        ROI: <span class="font-semibold <?= $statsGenerales['roi'] >= 0 ? 'text-green-500' : 'text-red-500' ?>"><?= $statsGenerales['roi'] ?>%</span>
                    </p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Inversión</span>
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-red-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">$<?= number_format($statsGenerales['gasto_total'], 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="fas fa-receipt mr-1"></i>
                        <?= $totalCampañas['activas'] ?> campañas activas
                    </p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Leads</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-plus text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= $totalLeads['total'] ?></p>
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="fas fa-check-circle text-blue-500 mr-1"></i>
                        <?= $totalLeads['ganados'] ?> ganados
                    </p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Conversiones</span>
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-concierge-bell text-purple-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800"><?= $statsGenerales['conversiones_totales'] ?></p>
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="fas fa-percentage mr-1"></i>
                        Tasa conv: <?= $statsGenerales['clicks_totales'] > 0 ? round($statsGenerales['conversiones_totales'] / $statsGenerales['clicks_totales'] * 100, 2) : 0 ?>%
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 fade-in">
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-slate-800">Ingresos vs Inversión</h3>
                        <span class="text-xs text-slate-400">Últimos 12 meses</span>
                    </div>
                    <canvas id="chartIngresos" height="280"></canvas>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-slate-800">Leads por Estado</h3>
                        <span class="text-xs text-slate-400">Total: <?= $totalLeads['total'] ?></span>
                    </div>
                    <canvas id="chartLeads" height="280"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 fade-in">
                <div class="bg-white rounded-2xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-slate-800">Rendimiento por Canal</h3>
                        <span class="text-xs text-slate-400">Ingresos vs Gasto</span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($canalesRendimiento as $canal): ?>
                            <?php $pctIngresos = $statsGenerales['ingresos_totales'] > 0 ? ($canal['ingresos'] / $statsGenerales['ingresos_totales'] * 100) : 0; ?>
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full" style="background: <?= htmlspecialchars($canal['color']) ?>"></span>
                                        <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($canal['nombre']) ?></span>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">$<?= number_format($canal['ingresos'], 0, ',', '.') ?></span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="progress-bar h-2 rounded-full" style="width: <?= $pctIngresos ?>%; background: <?= htmlspecialchars($canal['color']) ?>"></div>
                                </div>
                                <div class="flex justify-between text-xs text-slate-400 mt-0.5">
                                    <span><?= number_format($canal['clicks']) ?> clicks</span>
                                    <span><?= $canal['conversiones'] ?> conv.</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-slate-800">Top Campañas</h3>
                        <a href="<?= APP_URL ?>/pages/campanas.php" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Ver todas →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-400 text-xs uppercase tracking-wider">
                                    <th class="pb-3 font-medium">Campaña</th>
                                    <th class="pb-3 font-medium">Estado</th>
                                    <th class="pb-3 font-medium text-right">Ingresos</th>
                                    <th class="pb-3 font-medium text-right">ROI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCampañas as $c): ?>
                                <tr class="border-t border-slate-50">
                                    <td class="py-3 font-medium text-slate-700"><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            <?php
                                            $estados = ['planificada' => 'bg-slate-100 text-slate-600', 'activa' => 'bg-green-100 text-green-700', 'pausada' => 'bg-yellow-100 text-yellow-700', 'completada' => 'bg-blue-100 text-blue-700', 'cancelada' => 'bg-red-100 text-red-700'];
                                            echo $estados[$c['estado']] ?? 'bg-slate-100 text-slate-600';
                                            ?>">
                                            <?= ucfirst($c['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-right font-semibold text-slate-700">$<?= number_format($c['ingresos'], 0, ',', '.') ?></td>
                                    <td class="py-3 text-right font-semibold <?= $c['roi'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= $c['roi'] ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($visitasRecientes)): ?>
            <div class="bg-white rounded-2xl p-6 border border-slate-100 fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-slate-800">Tráfico del Sitio</h3>
                    <span class="text-xs text-slate-400">Visitantes vs Visitas</span>
                </div>
                <canvas id="chartTrafico" height="200"></canvas>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
const ingresosData = {
    labels: [<?php foreach ($ingresosPorMes as $m): ?>'<?= $m['mes'] ?>',<?php endforeach; ?>],
    datasets: [
        {
            label: 'Ingresos',
            data: [<?php foreach ($ingresosPorMes as $m): ?><?= $m['ingresos'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(34, 197, 94, 0.15)',
            borderColor: '#22c55e',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        },
        {
            label: 'Inversión',
            data: [<?php foreach ($ingresosPorMes as $m): ?><?= $m['gasto'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            borderColor: '#ef4444',
            borderWidth: 2,
            borderDash: [5, 5],
            fill: true,
            tension: 0.4
        }
    ]
};

new Chart(document.getElementById('chartIngresos'), {
    type: 'line',
    data: ingresosData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'top', labels: { usePointStyle: true, boxWidth: 6 } } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '$' + (v/1000).toFixed(0) + 'k' } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('chartLeads'), {
    type: 'doughnut',
    data: {
        labels: [<?php foreach ($leadsPorEstado as $l): ?>'<?= ucfirst($l['estado']) ?>',<?php endforeach; ?>],
        datasets: [{
            data: [<?php foreach ($leadsPorEstado as $l): ?><?= $l['total'] ?>,<?php endforeach; ?>],
            backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#14b8a6'],
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

<?php if (!empty($visitasRecientes)): ?>
new Chart(document.getElementById('chartTrafico'), {
    type: 'bar',
    data: {
        labels: [<?php foreach ($visitasRecientes as $v): ?>'<?= $v['dia'] ?>',<?php endforeach; ?>],
        datasets: [
            {
                label: 'Visitantes',
                data: [<?php foreach ($visitasRecientes as $v): ?><?= $v['visitantes'] ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderRadius: 4
            },
            {
                label: 'Visitas',
                data: [<?php foreach ($visitasRecientes as $v): ?><?= $v['visitas'] ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'top', labels: { usePointStyle: true, boxWidth: 6 } } },
        scales: {
            y: { beginAtZero: true },
            x: { grid: { display: false } }
        }
    }
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
