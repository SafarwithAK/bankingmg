<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM csp_users WHERE email = ?");
    $stmt->execute([$email]);
    $csp = $stmt->fetch();

    if($csp && password_verify($password, $csp['password'])){

        if($csp['status'] !== 'Active'){
            $error = "Your CSP account is inactive!";
        } else {

            session_regenerate_id(true);

            $_SESSION['csp_id'] = $csp['id'];
            $_SESSION['csp_name'] = $csp['name'];

            header("Location: csp_dashboard.php");
            exit;
        }

    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure CSP Access | MyBank</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #eff6ff 0%, #dbeafe 50%, #bfdbfe 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .input-group-focus:focus-within {
            ring: 2px;
            ring-color: #2563eb;
            border-color: #2563eb;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-up {
            animation: slideUp 0.5s ease-out forwards;
        }

        .mesh-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(225, 39%, 30%, 0.1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225, 39%, 20%, 0.05) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(225, 39%, 30%, 0.1) 0, transparent 50%);
        }
    </style>
</head>
<body class="flex flex-col">

    <div class="mesh-bg"></div>

    <!-- Navigation -->
    <nav class="w-full py-4 px-6 md:px-12 flex justify-between items-center bg-white/50 backdrop-blur-md border-b border-slate-200/50 sticky top-0 z-50">
        <div class="flex items-center space-x-2">
            <div class="bg-blue-600 p-2 rounded-xl text-white shadow-lg shadow-blue-200">
                <i class="bi bi-bank2 text-xl"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-slate-800">My<span class="text-blue-600">Bank</span></span>
        </div>
        <div class="hidden md:flex items-center space-x-6">
            <div class="flex items-center space-x-2 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold border border-emerald-100">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>Portal Active</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[420px] animate-slide-up">
            
            <!-- Branding/Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-4 text-blue-600">
                    <i class="bi bi-shield-lock-fill text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">CSP Portal Access</h1>
                <p class="text-slate-500 mt-1">Authorized Personnel Only</p>
            </div>

            <!-- Login Card -->
            <div class="glass-card rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    
                    <!-- Error Message -->
                    <?php if(!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center space-x-3 text-red-700 text-sm">
                        <i class="bi bi-exclamation-circle-fill text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-5">
                        <!-- Email Field -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">CSP Email Address</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input 
                                    type="email" 
                                    name="email" 
                                    required
                                    placeholder="name@mybank.com"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-sm font-semibold text-slate-700">Password</label>
                                <a href="csp_forgot_password.php" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">Forgot?</a>
                            </div>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input 
                                    type="password" 
                                    name="password" 
                                    required
                                    placeholder="••••••••"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button 
                            type="submit" 
                            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transform active:scale-[0.98] transition-all flex items-center justify-center space-x-2"
                        >
                            <span>Login</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>

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
            </div>

            <!-- Support Link -->
            <p class="text-center mt-8 text-sm text-slate-600">
                Having trouble logging in? <a href="#" class="font-semibold text-blue-600 hover:underline">Contact IT Support</a>
            </p>
        </div>
    </main>


</body>
</html>