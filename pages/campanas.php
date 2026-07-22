<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
$pdo = getDB();

$campanas = $pdo->query("
    SELECT ca.*, ch.nombre as canal_nombre, ch.color as canal_color,
           COALESCE(s.ingresos, 0) as ingresos,
           COALESCE(s.conversiones, 0) as conversiones,
           COALESCE(s.clicks, 0) as clicks,
           COALESCE(s.impresiones, 0) as impresiones
    FROM campanas ca
    LEFT JOIN canales ch ON ca.canal_id = ch.id
    LEFT JOIN (SELECT campana_id, SUM(ingresos) as ingresos, SUM(conversiones) as conversiones,
                      SUM(clicks) as clicks, SUM(impresiones) as impresiones
               FROM metricas_campana GROUP BY campana_id) s ON ca.id = s.campana_id
    ORDER BY ca.created_at DESC
")->fetchAll();
?>
<?php $titulo = 'Campañas'; include __DIR__ . '/../includes/header.php'; ?>
<div class="flex min-h-screen">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 ml-64 p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Campañas</h1>
                    <p class="text-slate-500 mt-1">Gestión de campañas de marketing</p>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-4 mb-6">
                <?php
                $resumenEstados = $pdo->query("SELECT estado, COUNT(*) as total FROM campanas GROUP BY estado")->fetchAll();
                $totalCamp = array_sum(array_column($resumenEstados, 'total'));
                $colores = ['planificada' => 'bg-slate-100 text-slate-600', 'activa' => 'bg-green-100 text-green-600', 'pausada' => 'bg-yellow-100 text-yellow-600', 'completada' => 'bg-blue-100 text-blue-600', 'cancelada' => 'bg-red-100 text-red-600'];
                $iconos = ['planificada' => 'calendar', 'activa' => 'play', 'pausada' => 'pause', 'completada' => 'check', 'cancelada' => 'xmark'];
                foreach ($resumenEstados as $re): ?>
                <div class="bg-white rounded-xl p-4 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 <?= $colores[$re['estado']] ?> rounded-lg flex items-center justify-center">
                            <i class="fas fa-<?= $iconos[$re['estado']] ?>"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-800"><?= $re['total'] ?></p>
                            <p class="text-xs text-slate-400 capitalize"><?= $re['estado'] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-4 font-medium">Campaña</th>
                                <th class="px-6 py-4 font-medium">Canal</th>
                                <th class="px-6 py-4 font-medium">Estado</th>
                                <th class="px-6 py-4 font-medium text-right">Presupuesto</th>
                                <th class="px-6 py-4 font-medium text-right">Inversión</th>
                                <th class="px-6 py-4 font-medium text-right">Ingresos</th>
                                <th class="px-6 py-4 font-medium text-right">Conversiones</th>
                                <th class="px-6 py-4 font-medium">Período</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($campanas as $c): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-800"><?= htmlspecialchars($c['nombre']) ?></p>
                                    <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($c['tipo']) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-sm">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background:<?= htmlspecialchars($c['canal_color']) ?>"></span>
                                        <?= htmlspecialchars($c['canal_nombre'] ?? 'Sin canal') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        <?= $colores[$c['estado']] ?? 'bg-slate-100 text-slate-600' ?>">
                                        <?= ucfirst($c['estado']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700">$<?= number_format($c['presupuesto'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right text-slate-600">$<?= number_format($c['inversion'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-semibold text-green-600">$<?= number_format($c['ingresos'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700"><?= $c['conversiones'] ?></td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    <?= $c['fecha_inicio'] ? date('d/m/Y', strtotime($c['fecha_inicio'])) : '-' ?>
                                    →
                                    <?= $c['fecha_fin'] ? date('d/m/Y', strtotime($c['fecha_fin'])) : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
