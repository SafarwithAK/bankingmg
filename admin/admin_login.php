<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $input = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getPDO();

    // username OR email login
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=? OR email=?");
    $stmt->execute([$input, $input]);
    $admin = $stmt->fetch();

    if($admin && password_verify($password, $admin['password'])){

        session_regenerate_id(true);

        $_SESSION['admin_id'] = $admin['id'];

        header("Location: admin_dashboard.php");
        exit;

    } else {
        $error = "Invalid username/email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Terminal | MyBank Secure</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.1; }
        }

        .animate-pulse-slow {
            animation: pulse-slow 8s infinite;
        }

        .mesh-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background-image: 
                radial-gradient(at 0% 0%, hsla(225, 39%, 30%, 0.3) 0, transparent 50%), 
                radial-gradient(at 100% 100%, hsla(225, 39%, 30%, 0.3) 0, transparent 50%);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide { animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="flex flex-col text-slate-200">

    <div class="mesh-bg animate-pulse-slow"></div>

    <!-- Navigation -->
    <nav class="w-full py-6 px-8 md:px-12 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center space-x-3">
            <div class="bg-indigo-600 p-2 rounded-xl text-white shadow-xl shadow-indigo-900/50">
                <i class="bi bi-shield-lock-fill text-xl"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold tracking-tight text-white leading-none">MyBank <span class="text-indigo-400">Admin</span></span>
                <span class="text-[9px] uppercase tracking-[0.3em] font-bold text-slate-500 mt-1">System Infrastructure</span>
            </div>
        </div>
        <div class="hidden md:flex items-center space-x-2">
            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
            <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-500/80">Network: Secure</span>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[420px] animate-slide">
            
            <!-- Icon/Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-800 rounded-3xl border border-slate-700 shadow-2xl mb-6 text-indigo-400">
                    <i class="bi bi-terminal-fill text-4xl"></i>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight">Terminal Access</h1>
                <p class="text-slate-400 mt-2 text-sm font-medium">Verify credentials for system management</p>
            </div>

            <!-- Login Card -->
            <div class="glass-card rounded-[2.5rem] shadow-2xl overflow-hidden border border-white/5">
                <div class="p-10">
                    
                    <!-- Error Message -->
                    <?php if(!empty($error)): ?>
                    <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center space-x-3 text-red-400 text-xs font-semibold">
                        <i class="bi bi-shield-exclamation text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <!-- Username Field -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 ml-1">Administrator ID</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                    <i class="bi bi-person-badge"></i>
                                </span>
                                <input 
                                    type="text" 
                                    name="username" 
                                    required
                                    placeholder="Enter username or email"
                                    class="w-full pl-12 pr-6 py-4 bg-slate-900/50 border border-slate-800 rounded-2xl text-white placeholder:text-slate-600 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500/50 transition-all font-medium"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Secret Key</label>
                                <a href="admin_forgot_password.php" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300 transition">Recover Key</a>
                            </div>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                    <i class="bi bi-key-fill"></i>
                                </span>
                                <input 
                                    type="password" 
                                    name="password" 
                                    required
                                    placeholder="••••••••••••"
                                    class="w-full pl-12 pr-6 py-4 bg-slate-900/50 border border-slate-800 rounded-2xl text-white placeholder:text-slate-600 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500/50 transition-all font-medium"
                                >
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full py-5 bg-indigo-600 hover:bg-indigo-500 text-white font-black rounded-2xl shadow-xl shadow-indigo-900/20 transform active:scale-[0.98] transition-all flex items-center justify-center space-x-3 mt-4"
                        >
                            <i class="bi bi-cpu"></i>
                            <span class="uppercase tracking-widest text-sm">Login</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>

 
                <!-- Footer Links -->
                <div class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 flex flex-col items-center space-y-3">
                    <div class="flex items-center space-x-2 text-[10px] uppercase tracking-widest font-bold text-slate-400">
                        <i class="bi bi-patch-check"></i>
                        <span>Secure End-to-End Encryption</span>
                    </div>
                    <p class="text-xs text-slate-500">
                        © 2026 MyBank Systems. All rights reserved.
                    </p>
                </div>

</body>
</html>