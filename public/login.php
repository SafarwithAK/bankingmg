<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password_hash'])){

        $stmt2 = $pdo->prepare("SELECT id FROM accounts WHERE user_id = ? AND status = 'Active'");
        $stmt2->execute([$user['id']]);
        $activeAccount = $stmt2->fetch();

        if(!$activeAccount){
            $error = "Your account is inactive. Please contact branch.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        }

    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | MyBank</title>
    
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
            <a href="register.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition">Open an Account</a>
            <div class="h-4 w-[1px] bg-slate-200"></div>
            <div class="flex items-center space-x-2 text-slate-500 text-xs font-medium">
                <i class="bi bi-shield-check text-blue-600"></i>
                <span>Secure Banking Environment</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[420px] animate-slide-up">
            
            <!-- Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-4 text-blue-600">
                    <i class="bi bi-person-circle text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Customer Login </h1>
                <p class="text-slate-500 mt-1">Access your secure banking dashboard</p>
            </div>

            <!-- Login Card -->
            <div class="glass-card rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    
                    <!-- Status Messages -->
                    <?php if(!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start space-x-3 text-red-700 text-sm">
                        <i class="bi bi-exclamation-circle-fill mt-0.5"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['registered'])): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center space-x-3 text-emerald-700 text-sm">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Account created successfully! Please log in.</span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-5">
                        <!-- Username/Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Username or Email</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input 
                                    type="text" 
                                    name="username" 
                                    required
                                    placeholder="Enter username or email"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-sm font-semibold text-slate-700">Password</label>
                                <a href="forgot_password.php" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">Forgot Password?</a>
                            </div>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="passwordInput"
                                    required
                                    placeholder="••••••••"
                                    class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                                <button 
                                    type="button"
                                    onclick="togglePasswordVisibility()"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                >
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button 
                            type="submit" 
                            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transform active:scale-[0.98] transition-all flex items-center justify-center space-x-2"
                        >
                            <span>Login</span>
                            <i class="bi bi-box-arrow-in-right"></i>
                        </button>
                    </form>
                </div>

                <!-- Registration Prompt -->
                <div class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-600">
                        New to MyBank? 
                        <a href="register.php" class="font-bold text-blue-600 hover:text-blue-700 transition inline-flex items-center space-x-1">
                            <span>Open Account</span>
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="p-6 text-center text-xs text-slate-500">
        © 2026 MyBank Banking Infrastructure. PCI-DSS Certified.
    </footer>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>

</body>
</html>