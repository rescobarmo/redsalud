<?php
require_once __DIR__ . '/includes/auth.php';

if (usuarioAutenticado()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $resultado = intentarLogin($email, $password);
    if ($resultado['success']) {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
    $error = $resultado['message'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .login-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .input-field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.6);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/20 rounded-2xl mb-4">
                <i class="fas fa-chart-line text-3xl text-blue-400"></i>
            </div>
            <h1 class="text-3xl font-bold text-white"><?= APP_NAME ?></h1>
            <p class="text-slate-400 mt-1">Panel de control de marketing</p>
        </div>

        <div class="glass-card rounded-2xl p-8">
            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 mb-6 text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2" for="email">
                        <i class="fas fa-envelope mr-1 text-slate-500"></i> Correo electrónico
                    </label>
                    <input type="email" id="email" name="email" required
                           class="input-field w-full rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none"
                           placeholder="tu@correo.cl" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2" for="password">
                        <i class="fas fa-lock mr-1 text-slate-500"></i> Contraseña
                    </label>
                    <input type="password" id="password" name="password" required
                           class="input-field w-full rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none"
                           placeholder="••••••••">
                </div>
                <button type="submit"
                        class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2">
                    <i class="fas fa-right-to-bracket"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/10">
                <p class="text-xs text-slate-500 text-center">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Credenciales demo: <span class="text-slate-400">admin@redsalud.cl</span> / <span class="text-slate-400">password</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
