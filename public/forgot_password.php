<?php
require_once __DIR__ . '/../src/db.php';

$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $email = trim($_POST['email']);
    $pass1 = $_POST['password'];
    $pass2 = $_POST['confirm_password'];

    if($pass1 !== $pass2){
        $error = "Passwords do not match!";
    } 
    elseif(strlen($pass1) < 6){
        $error = "Password must be at least 6 characters!";
    } 
    else {

        $pdo = getPDO();

        // Check user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user){

            $newPassword = password_hash($pass1, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newPassword, $user['id']]);

            $success = "Password reset successful! You can login now.";

        } else {
            $error = "Email not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | MyBank Secure</title>
    
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
        <a href="login.php" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition flex items-center space-x-2">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Login</span>
        </a>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[440px] animate-slide-up">
            
            <!-- Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-4 text-blue-600">
                    <i class="bi bi-shield-lock-fill text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Account Recovery</h1>
                <p class="text-slate-500 mt-1">Reset your password to secure access</p>
            </div>

            <!-- Card -->
            <div class="glass-card rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    
                    <!-- Feedback Messages -->
                    <?php if(!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center space-x-3 text-red-700 text-sm">
                        <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($success)): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center space-x-3 text-emerald-700 text-sm">
                        <i class="bi bi-check-circle-fill text-lg"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-5">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Verification Email</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-envelope-check"></i>
                                </span>
                                <input 
                                    type="email" 
                                    name="email" 
                                    required
                                    placeholder="Enter your registered email"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-key-fill"></i>
                                </span>
                                <input 
                                    type="password" 
                                    name="password" 
                                    required
                                    placeholder="Minimum 6 characters"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-shield-check"></i>
                                </span>
                                <input 
                                    type="password" 
                                    name="confirm_password" 
                                    required
                                    placeholder="Repeat new password"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transform active:scale-[0.98] transition-all flex items-center justify-center space-x-2"
                        >
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Submit</span>
                        </button>
                    </form>
                </div>

                <!-- Footer Link -->
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-500">
                        Suddenly remembered? <a href="login.php" class="font-bold text-blue-600 hover:underline">Login</a>
                    </p>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="text-center mt-8">
                <div class="inline-flex items-center space-x-4 bg-white/40 px-4 py-2 rounded-full border border-white/50 text-[11px] text-slate-500">
                    <span class="flex items-center space-x-1"><i class="bi bi-lock-fill"></i> <span>256-bit Secure Session</span></span>
                    <span class="text-slate-300">|</span>
                    <span class="flex items-center space-x-1"><i class="bi bi-eye-slash-fill"></i> <span>Privacy Protected</span></span>
                </div>
            </div>
        </div>
    </main>

    <footer class="p-6 text-center text-xs text-slate-400">
        © 2026 MyBank Banking Infrastructure. Security logging enabled.
    </footer>

</body>
</html>