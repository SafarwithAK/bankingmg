<?php

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
requireLogin();

$amount = (float)($_POST['amount'] ?? 0);

if($amount <= 0){
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment | MyBank Secure</title>
    
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

        .payment-option {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .payment-option.active {
            border-color: #2563eb;
            background-color: #eff6ff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.98) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .animate-content {
            animation: fadeInScale 0.4s ease-out forwards;
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
            <button onclick="window.history.back()" class="text-sm font-semibold text-slate-600 hover:text-red-600 transition flex items-center space-x-2">
                <i class="bi bi-x-lg"></i>
                <span class="hidden sm:inline">Cancel Transaction</span>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-[550px] animate-content">
            
            <!-- Summary Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Complete Your Payment</h1>
                <p class="text-slate-500 mt-1">Select your preferred method and securely finalize payment</p>
                
                <div class="mt-6 inline-flex items-center space-x-4 bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100">
                    <span class="text-sm font-medium text-slate-500">Payable Amount:</span>
                    <span class="text-2xl font-bold text-blue-600">₹<?= number_format($amount, 2) ?></span>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="glass-card rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    
                    <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Choose Payment Method</h2>

                    <!-- Selection Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
                        <button onclick="selectMethod('upi', this)" class="payment-option active flex flex-col items-center justify-center p-4 border-2 border-slate-100 rounded-2xl hover:border-blue-200 group">
                            <i class="bi bi-qr-code-scan text-2xl mb-2 text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                            <span class="text-sm font-bold text-slate-700">UPI</span>
                        </button>
                        
                        <button onclick="selectMethod('card', this)" class="payment-option flex flex-col items-center justify-center p-4 border-2 border-slate-100 rounded-2xl hover:border-blue-200 group">
                            <i class="bi bi-credit-card text-2xl mb-2 text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                            <span class="text-sm font-bold text-slate-700">Cards</span>
                        </button>
                        
                        <button onclick="selectMethod('bank', this)" class="payment-option flex flex-col items-center justify-center p-4 border-2 border-slate-100 rounded-2xl hover:border-blue-200 group">
                            <i class="bi bi-building-columns text-2xl mb-2 text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                            <span class="text-sm font-bold text-slate-700">Banking</span>
                        </button>
                    </div>

                    <div class="relative">
                        <!-- UPI FORM -->
                        <div id="upi-container" class="method-content space-y-4">
                            <form action="payment_process.php" method="POST">
                                <input type="hidden" name="amount" value="<?= $amount ?>">
                                <input type="hidden" name="method" value="upi">
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Virtual Payment Address (VPA)</label>
                                        <div class="relative group">
                                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                                <i class="bi bi-at"></i>
                                            </span>
                                            <input 
                                                type="text" 
                                                name="upi_id" 
                                                required
                                                placeholder="username@bank"
                                                class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                                            >
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center space-x-2">
                                        <span>Proceed with UPI</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- CARD FORM -->
                        <div id="card-container" class="method-content hidden space-y-4">
                            <form action="payment_process.php" method="POST">
                                <input type="hidden" name="amount" value="<?= $amount ?>">
                                <input type="hidden" name="method" value="card">
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Card Number</label>
                                        <input type="text" name="card_number" required placeholder="XXXX XXXX XXXX XXXX" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Expiry Date</label>
                                            <input type="text" required placeholder="MM/YY" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">CVV</label>
                                            <input type="password" required placeholder="•••" maxlength="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 transition-all flex items-center justify-center space-x-2">
                                        <span>Pay ₹<?= number_format($amount, 2) ?></span>
                                        <i class="bi bi-shield-lock"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- BANK FORM -->
                        <div id="bank-container" class="method-content hidden space-y-4">
                            <form action="payment_process.php" method="POST">
                                <input type="hidden" name="amount" value="<?= $amount ?>">
                                <input type="hidden" name="method" value="netbanking">
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Select Your Bank</label>
                                        <select name="bank" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                                            <option value="sbi">State Bank of India</option>
                                            <option value="hdfc">HDFC Bank</option>
                                            <option value="icici">ICICI Bank</option>
                                            <option value="axis">Axis Bank</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center space-x-2">
                                        <span>Proceed to Net Banking</span>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex justify-center space-x-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo.png/640px-UPI-Logo.png" class="h-4 grayscale opacity-50" alt="UPI">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" class="h-4 grayscale opacity-50" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" class="h-4 grayscale opacity-50" alt="Mastercard">
                </div>
            </div>

            <p class="text-center mt-8 text-xs text-slate-500 font-medium tracking-tight">
                Secure 256-bit encrypted gateway. Your data is never stored.
            </p>
        </div>
    </main>

    <script>
        function selectMethod(id, element) {
            // Remove active classes from all options
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('active');
                opt.querySelector('i').classList.replace('text-blue-500', 'text-slate-400');
            });

            // Add active class to clicked option
            element.classList.add('active');
            element.querySelector('i').classList.replace('text-slate-400', 'text-blue-500');

            // Hide all form containers
            document.querySelectorAll('.method-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected form container with a subtle animation reset
            const target = document.getElementById(id + '-container');
            target.classList.remove('hidden');
        }
    </script>

</body>
</html>