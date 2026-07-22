<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
$pdo = getDB();

$filtroEstado = $_GET['estado'] ?? '';
$filtroBusqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT l.*, ca.nombre as campana_nombre, ch.nombre as canal_nombre, ch.color as canal_color
        FROM leads l
        LEFT JOIN campanas ca ON l.campana_id = ca.id
        LEFT JOIN canales ch ON l.canal_id = ch.id";
$where = [];
$params = [];

if ($filtroEstado) {
    $where[] = "l.estado = ?";
    $params[] = $filtroEstado;
}
if ($filtroBusqueda) {
    $where[] = "(l.nombre LIKE ? OR l.apellido LIKE ? OR l.email LIKE ? OR l.empresa LIKE ?)";
    $params[] = "%$filtroBusqueda%";
    $params[] = "%$filtroBusqueda%";
    $params[] = "%$filtroBusqueda%";
    $params[] = "%$filtroBusqueda%";
}
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();
?>
<?php $titulo = 'Leads'; include __DIR__ . '/../includes/header.php'; ?>
<div class="flex min-h-screen">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 ml-64 p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Leads</h1>
                    <p class="text-slate-500 mt-1">Gestión de prospectos y clientes potenciales</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 p-5 mb-6">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="busqueda" value="<?= htmlspecialchars($filtroBusqueda) ?>"
                               placeholder="Buscar por nombre, email o empresa..."
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none text-sm">
                    </div>
                    <select name="estado" class="px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none text-sm bg-white">
                        <option value="">Todos los estados</option>
                        <?php foreach (['nuevo','contactado','calificado','propuesta','negociacion','ganado','perdido'] as $e): ?>
                            <option value="<?= $e ?>" <?= $filtroEstado === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <?php if ($filtroEstado || $filtroBusqueda): ?>
                        <a href="leads.php" class="px-4 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-4 font-medium">Nombre</th>
                                <th class="px-6 py-4 font-medium">Contacto</th>
                                <th class="px-6 py-4 font-medium">Empresa</th>
                                <th class="px-6 py-4 font-medium">Campaña</th>
                                <th class="px-6 py-4 font-medium">Canal</th>
                                <th class="px-6 py-4 font-medium">Estado</th>
                                <th class="px-6 py-4 font-medium text-right">Score</th>
                                <th class="px-6 py-4 font-medium">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($leads)): ?>
                                <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">No se encontraron leads</td></tr>
                            <?php endif; ?>
                            <?php foreach ($leads as $l): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: <?= $l['canal_color'] ?? '#6B7280' ?>">
                                            <?= strtoupper(substr($l['nombre'], 0, 1) . substr($l['apellido'] ?? '', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800"><?= htmlspecialchars($l['nombre'] . ' ' . ($l['apellido'] ?? '')) ?></p>
                                            <p class="text-xs text-slate-400"><?= htmlspecialchars($l['cargo'] ?? '') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-700"><?= htmlspecialchars($l['email'] ?? '') ?></p>
                                    <p class="text-xs text-slate-400"><?= htmlspecialchars($l['telefono'] ?? '') ?></p>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700"><?= htmlspecialchars($l['empresa'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($l['campana_nombre'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($l['canal_nombre']): ?>
                                    <span class="inline-flex items-center gap-1.5 text-sm">
                                        <span class="w-2 h-2 rounded-full" style="background:<?= htmlspecialchars($l['canal_color']) ?>"></span>
                                        <?= htmlspecialchars($l['canal_nombre']) ?>
                                    </span>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        $colorEstado = ['nuevo'=>'bg-blue-100 text-blue-700','contactado'=>'bg-yellow-100 text-yellow-700','calificado'=>'bg-purple-100 text-purple-700','propuesta'=>'bg-indigo-100 text-indigo-700','negociacion'=>'bg-orange-100 text-orange-700','ganado'=>'bg-green-100 text-green-700','perdido'=>'bg-red-100 text-red-700'];
                                        echo $colorEstado[$l['estado']] ?? 'bg-slate-100 text-slate-600';
                                        ?>">
                                        <?= ucfirst($l['estado']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center gap-1 font-semibold <?= $l['score'] >= 70 ? 'text-green-600' : ($l['score'] >= 40 ? 'text-yellow-600' : 'text-slate-400') ?>">
                                        <i class="fas fa-bolt text-xs"></i>
                                        <?= $l['score'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?= date('d/m/Y', strtotime($l['created_at'])) ?></td>
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
