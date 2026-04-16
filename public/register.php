<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

$pdo = getPDO(); // ✅ FIX: top pe hi define

$error = "";

// ================= REGISTER =================
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $mobile = $_POST['mobile'] ?? null;
    $address = $_POST['address'] ?? null;
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $nominee = $_POST['nominee_name'] ?? null;
    $branch_id = $_POST['branch_id'] ?? null;

    if(strlen($password) < 6){
        $error = "Password should be minimum 6 characters.";
    } else {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->execute([$username, $email]);

        if($stmt->fetch()){
            $error = "Username or email already exists.";
        } else {

            $pdo->beginTransaction();

            try{
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users 
                    (username, email, full_name, password_hash, mobile, address, dob, gender, nominee_name, branch_id)
                    VALUES (?,?,?,?,?,?,?,?,?,?)
                ");
                $stmt->execute([
                    $username,
                    $email,
                    $full_name,
                    $passwordHash,
                    $mobile,
                    $address,
                    $dob,
                    $gender,
                    $nominee,
                    $branch_id
                ]);

                $userId = $pdo->lastInsertId();

                $accNumber = generateAccountNumber();

                $stmt = $pdo->prepare("
                    INSERT INTO accounts (user_id, account_number, balance)
                    VALUES (?,?,?)
                ");
                $stmt->execute([$userId, $accNumber, 1000.00]);

                $pdo->commit();

                header('Location: login.php?registered=1');
                exit;

            } catch(Exception $e){
                $pdo->rollBack();
                $error = "Something went wrong!";
            }
        }
    }
}

// ================= FETCH BRANCHES =================
$stmt = $pdo->query("SELECT id, branch_name, ifsc_code FROM branches ORDER BY branch_name ASC");
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Account | MyBank Digital</title>
    
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

        .input-focus {
            transition: all 0.2s;
        }

        .input-focus:focus {
            ring: 4px;
            ring-color: rgba(37, 99, 235, 0.1);
            border-color: #2563eb;
            background-color: #fff;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out forwards;
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
        <div class="flex items-center space-x-4">
            <span class="hidden sm:inline text-xs font-semibold text-slate-500 uppercase tracking-widest">Already have an account?</span>
            <a href="login.php" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-xl transition">Login</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="w-full max-w-[650px] animate-slide-in">
            
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-4 text-blue-600">
                    <i class="bi bi-person-circle text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-slate-800">Open New Account</h1>
                <p class="text-slate-500 mt-2">Join MyBank and experience next-level digital banking</p>
            </div>

            <!-- Registration Card -->
            <div class="glass-card rounded-[2.5rem] shadow-2xl overflow-hidden mb-12">
                <div class="p-8 sm:p-12">
                    
                    <!-- Error Message -->
                    <?php if(!empty($error)): ?>
                    <div class="mb-8 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start space-x-3 text-red-700 text-sm">
                        <i class="bi bi-exclamation-octagon-fill mt-0.5"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-8">
                        
                        <!-- Section: Credentials -->
                        <div>
                            <div class="flex items-center space-x-2 mb-6 text-slate-400">
                                <i class="bi bi-shield-lock"></i>
                                <span class="text-xs font-bold uppercase tracking-[0.2em]">Security Credentials</span>
                                <div class="flex-grow h-[1px] bg-slate-100"></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                                    <input type="text" name="username" required placeholder="Enter Username" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                                    <input type="password" name="password" required placeholder="Min. 6 characters" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Section: Personal Info -->
                        <div>
                            <div class="flex items-center space-x-2 mb-6 text-slate-400">
                                <i class="bi bi-person-badge"></i>
                                <span class="text-xs font-bold uppercase tracking-[0.2em]">Personal Information</span>
                                <div class="flex-grow h-[1px] bg-slate-100"></div>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                                    <input type="text" name="full_name" required placeholder="Full Name" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                                        <input type="email" name="email" required placeholder="example@gmail.com" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mobile Number</label>
                                        <input type="text" name="mobile" placeholder="+91 XXXXX XXXXX" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Date of Birth</label>
                                        <input type="date" name="dob" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Gender</label>
                                        <select name="gender" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                                            <option value="">Select Option</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Permanent Address</label>
                                    <textarea name="address" rows="3" placeholder="Street, City, State, Zip" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Account Config -->
                        <div>
                            <div class="flex items-center space-x-2 mb-6 text-slate-400">
                                <i class="bi bi-bank"></i>
                                <span class="text-xs font-bold uppercase tracking-[0.2em]">Banking Preferences</span>
                                <div class="flex-grow h-[1px] bg-slate-100"></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Preferred Branch</label>
                                    <select name="branch_id" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                                        <option value="">Choose Branch</option>
                                        <?php foreach($branches as $b): ?>
                                            <option value="<?= $b['id'] ?>">
                                                <?= htmlspecialchars($b['branch_name']) ?> (<?= $b['ifsc_code'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nominee Name</label>
                                    <input type="text" name="nominee_name" placeholder="Optional" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <button 
                                type="submit" 
                                class="w-full py-4.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-[1.25rem] shadow-xl shadow-blue-200 transition-all transform active:scale-[0.98] flex items-center justify-center space-x-2 py-4"
                            >
                                <i class="bi bi-person-check text-lg"></i>
                                <span>Open Account Now</span>
                            </button>
                            <p class="text-center text-[11px] text-slate-400 mt-5 leading-relaxed">
                                By clicking Open Account Now, you agree to our Terms of Service and Privacy Policy.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="p-8 text-center border-t border-slate-200/30 bg-white/20 backdrop-blur-sm">
        <div class="flex flex-wrap justify-center gap-6 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4">
            <span class="flex items-center space-x-1"><i class="bi bi-shield-shaded text-blue-500"></i> <span>256-bit AES Encryption</span></span>
            <span class="flex items-center space-x-1"><i class="bi bi-patch-check-fill text-emerald-500"></i> <span>Identity Verified</span></span>
            <span class="flex items-center space-x-1"><i class="bi bi-lock-fill text-blue-500"></i> <span>Secure Session</span></span>
        </div>
        <p class="text-xs text-slate-500">© 2026 MyBank Banking Infrastructure. Accessing this system implies consent to monitoring.</p>
    </footer>

</body>
</html>