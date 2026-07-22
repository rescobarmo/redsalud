<?php
require_once __DIR__ . '/includes/auth.php';
requerirLogin();

$pdo = getDB();
$usuario = usuarioActual();
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
                <div></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 fade-in">
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ingresos</span>
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">$0</p>
                    <p class="text-xs text-slate-400 mt-1">ROI: <span class="font-semibold text-slate-400">0%</span></p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Inversión</span>
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-red-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">$0</p>
                    <p class="text-xs text-slate-400 mt-1">0 campañas activas</p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Leads</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-plus text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">0</p>
                    <p class="text-xs text-slate-400 mt-1">0 ganados</p>
                </div>
                <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Conversiones</span>
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-concierge-bell text-purple-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">0</p>
                    <p class="text-xs text-slate-400 mt-1">Tasa conv: 0%</p>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
