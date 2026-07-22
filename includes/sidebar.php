<?php
$usuario = usuarioActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<aside class="fixed left-0 top-0 h-full w-64 bg-slate-900 z-50 sidebar overflow-y-auto">
    <div class="p-6">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <div>
                <h2 class="text-white font-bold text-lg leading-tight">Marketing</h2>
                <p class="text-slate-400 text-xs">Dashboard v<?= APP_VERSION ?></p>
            </div>
        </div>

        <div class="flex items-center gap-3 px-3 py-3 bg-white/5 rounded-xl mb-6">
            <div class="w-9 h-9 bg-blue-500/20 rounded-full flex items-center justify-center">
                <span class="text-blue-400 font-semibold text-sm">
                    <?= strtoupper(substr($usuario['nombre'], 0, 2)) ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($usuario['nombre']) ?></p>
                <p class="text-slate-400 text-xs"><?= htmlspecialchars($usuario['rol_nombre']) ?></p>
            </div>
        </div>

        <nav class="space-y-1">
            <a href="<?= APP_URL ?>/dashboard.php"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm <?= $paginaActual === 'dashboard.php' ? 'active text-white' : 'text-slate-300' ?>">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                Dashboard
            </a>
            <a href="<?= APP_URL ?>/pages/campanas.php"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm <?= $paginaActual === 'campanas.php' ? 'active text-white' : 'text-slate-300' ?>">
                <i class="fas fa-bullhorn w-5 text-center"></i>
                Campañas
            </a>
            <a href="<?= APP_URL ?>/pages/leads.php"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm <?= $paginaActual === 'leads.php' ? 'active text-white' : 'text-slate-300' ?>">
                <i class="fas fa-users w-5 text-center"></i>
                Leads
            </a>
            <a href="<?= APP_URL ?>/pages/analytics.php"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm <?= $paginaActual === 'analytics.php' ? 'active text-white' : 'text-slate-300' ?>">
                <i class="fas fa-chart-bar w-5 text-center"></i>
                Analíticas
            </a>
            <hr class="border-slate-700 my-4">
            <a href="<?= APP_URL ?>/logout.php"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-400 hover:text-white">
                <i class="fas fa-right-from-bracket w-5 text-center"></i>
                Cerrar Sesión
            </a>
        </nav>
    </div>
</aside>
