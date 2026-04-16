<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$pdo = getPDO();
$error = "";
$step = 1;

// STEP 1
if(isset($_POST['check_email'])){
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM csp_users WHERE email=?");
    $stmt->execute([$email]);
    $csp = $stmt->fetch();

    if($csp){
        $_SESSION['reset_csp'] = $csp['id'];
        $step = 2;
    } else {
        $error = "Email not found!";
    }
}

// STEP 2
if(isset($_POST['reset_pass']) && isset($_SESSION['reset_csp'])){
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if($new !== $confirm){
        $error = "Passwords do not match!";
        $step = 2;
    } elseif(strlen($new) < 6){
        $error = "Password must be at least 6 characters!";
        $step = 2;
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE csp_users SET password=? WHERE id=?");
        $stmt->execute([$hash, $_SESSION['reset_csp']]);

        unset($_SESSION['reset_csp']);

        header("Location: csp_login.php?reset=success");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | MyBank CSP</title>
    
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
            <span class="text-xl font-bold tracking-tight text-slate-800">My<span class="text-blue-600">Bank</span> <span class="text-slate-400 font-medium">CSP</span></span>
        </div>
        <a href="csp_login.php" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition flex items-center space-x-2">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Login</span>
        </a>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[440px] animate-slide-up">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-4 text-blue-600">
                    <i class="bi bi-key-fill text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Account Recovery</h1>
                <p class="text-slate-500 mt-1">
                    <?php echo ($step === 1) ? 'Enter your email to verify your identity' : 'Create a strong new password'; ?>
                </p>
            </div>

            <!-- Card -->
            <div class="glass-card rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    
                    <!-- Error Message -->
                    <?php if(!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center space-x-3 text-red-700 text-sm">
                        <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if($step === 1): ?>
                    <!-- STEP 1: Email Verification -->
                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Registered Email Address</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-envelope-at"></i>
                                </span>
                                <input 
                                    type="email" 
                                    name="email" 
                                    required
                                    placeholder="Example@gmail.com"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            name="check_email"
                            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transform active:scale-[0.98] transition-all flex items-center justify-center space-x-2"
                        >
                            <span>Verify Identity</span>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </form>

                    <?php else: ?>
                    <!-- STEP 2: New Password -->
                    <form method="POST" action="" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    required
                                    placeholder="Minimum 6 characters"
                                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm New Password</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="bi bi-shield-lock"></i>
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
                            name="reset_pass"
                            class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 hover:shadow-emerald-300 transform active:scale-[0.98] transition-all flex items-center justify-center space-x-2"
                        >
                            <i class="bi bi-check2-circle"></i>
                            <span>Update Security Credentials</span>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <!-- Step Indicator -->
                <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                    <div class="flex space-x-1.5">
                        <div class="h-1.5 w-8 rounded-full <?php echo ($step === 1) ? 'bg-blue-600' : 'bg-blue-200'; ?>"></div>
                        <div class="h-1.5 w-8 rounded-full <?php echo ($step === 2) ? 'bg-blue-600' : 'bg-blue-200'; ?>"></div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Step <?php echo $step; ?> of 2
                    </span>
                </div>
            </div>

            <!-- Support Link -->
            <div class="text-center mt-8 space-y-4">
                <p class="text-sm text-slate-600">
                    Remembered your password? <a href="csp_login.php" class="font-semibold text-blue-600 hover:underline">Login</a>
                </p>
                <div class="inline-flex items-center space-x-4 bg-white/40 px-4 py-2 rounded-full border border-white/50 text-[11px] text-slate-500">
                    <span class="flex items-center space-x-1"><i class="bi bi-shield-check"></i> <span>Identity Verified Access</span></span>
                </div>
            </div>
        </div>
    </main>

    <footer class="p-6 text-center text-xs text-slate-500">
        © 2026 MyBank Banking Infrastructure. Access monitored for security.
    </footer>

</body>
</html>