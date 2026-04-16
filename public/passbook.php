<?php
require_once __DIR__ . '/../src/db.php';
session_start();

if(!isset($_SESSION['user_id'])){
    die("Unauthorized");
}

$pdo = getPDO();

$stmt = $pdo->prepare("
SELECT 
    u.full_name, u.email, u.username, u.created_at,
    u.mobile, u.address, u.dob, u.gender, u.nominee_name,
    a.account_number,
    b.branch_name, b.ifsc_code
FROM users u
JOIN accounts a ON u.id = a.user_id
LEFT JOIN branches b ON u.branch_id = b.id
WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$data = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Passbook | MyBank Secure</title>
    
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .passbook-page {
            width: 820px;
            min-height: 500px;
            background: white;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        /* Bank Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100px;
            font-weight: 900;
            color: rgba(30, 58, 138, 0.04);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 0.1em;
        }

        .mesh-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(225, 39%, 30%, 0.1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(225, 39%, 30%, 0.1) 0, transparent 50%);
        }

        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .passbook-page { 
                box-shadow: none !important; 
                border: 2px solid #1e3a8a !important;
                margin: 0 !important;
                width: 100% !important;
                page-break-after: always;
            }
            .mesh-bg { display: none; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade { animation: fadeIn 0.5s ease-out forwards; }
    </style>
</head>
<body class="flex flex-col p-0 md:p-6 lg:p-10">

    <div class="mesh-bg"></div>

    <!-- Navigation (No Print) -->
    <nav class="no-print w-full max-w-5xl mx-auto py-4 px-6 mb-8 flex justify-between items-center bg-white/50 backdrop-blur-md border border-white/50 rounded-3xl sticky top-4 z-50">
        <div class="flex items-center space-x-2">
            <div class="bg-blue-600 p-2 rounded-xl text-white shadow-lg shadow-blue-200">
                <i class="bi bi-bank2 text-xl"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-slate-800">My<span class="text-blue-600">Bank</span></span>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-100">
                <i class="bi bi-printer"></i>
                <span>Print Passbook</span>
            </button>
            <a href="dashboard.php" class="text-sm font-bold text-slate-600 hover:text-blue-600 px-4 py-2 transition">Dashboard</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex flex-col items-center space-y-10 pb-20 animate-fade">
        
        <!-- Header Info (No Print) -->
        <div class="no-print text-center mb-4">
            <h1 class="text-3xl font-bold text-slate-800">Your Digital Passbook</h1>
            <p class="text-slate-500 mt-2">Official record of your account credentials and branch details</p>
        </div>

        <!-- PAGE 1: Bank & Branch Info -->
        <div class="passbook-page rounded-3xl p-10 flex flex-col relative">
            <div class="watermark uppercase">My Bank</div>
            
            <div class="relative z-10">
                <!-- Passbook Header -->
                <div class="flex justify-between items-center border-b-2 border-blue-900 pb-6 mb-8">
                    <div class="flex items-center space-x-3">
                        <i class="bi bi-bank2 text-4xl text-blue-900"></i>
                        <div>
                            <h2 class="text-2xl font-black text-blue-900 tracking-tight">MY BANK</h2>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Digital Banking Infrastructure</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-900">PASSBOOK FRONT PAGE</p>
                        <p class="text-[10px] text-slate-400 font-medium">Original Account Copy</p>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-10">
                    <!-- Left: Guidelines -->
                    <div class="col-span-4 space-y-8">
                        <div>
                            <h3 class="text-xs font-black text-blue-900 uppercase tracking-[0.2em] mb-4">Security Guidelines</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start space-x-3 text-xs text-slate-600 leading-relaxed">
                                    <i class="bi bi-shield-check text-blue-600"></i>
                                    <span>Never share your OTP, CVV, or Login Password with anyone.</span>
                                </li>
                                <li class="flex items-start space-x-3 text-xs text-slate-600 leading-relaxed">
                                    <i class="bi bi-shield-check text-blue-600"></i>
                                    <span>Bank officials will never call you asking for sensitive credentials.</span>
                                </li>
                                <li class="flex items-start space-x-3 text-xs text-slate-600 leading-relaxed">
                                    <i class="bi bi-shield-check text-blue-600"></i>
                                    <span>Report unauthorized transactions immediately to our 24/7 helpline.</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-blue-900 uppercase tracking-[0.2em] mb-4">Support Contact</h3>
                            <div class="space-y-2 text-xs text-slate-600">
                                <p class="flex items-center space-x-2"><i class="bi bi-telephone-fill text-blue-600"></i> <span>1800-111-222 (Toll Free)</span></p>
                                <p class="flex items-center space-x-2"><i class="bi bi-envelope-fill text-blue-600"></i> <span>support@mybank.com</span></p>
                                <p class="flex items-center space-x-2"><i class="bi bi-globe text-blue-600"></i> <span>www.mybank.com</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Center: Photo Slot -->
                    <div class="col-span-3 flex flex-col items-center justify-center">
                        <div class="w-40 h-52 border-2 border-dashed border-blue-200 rounded-2xl bg-slate-50 flex items-center justify-center text-center p-6">
                            <div class="text-slate-300">
                                <i class="bi bi-person-bounding-box text-5xl mb-2"></i>
                                <p class="text-[10px] font-bold uppercase tracking-widest">Affix Account Holder Photo</p>
                            </div>
                        </div>
                        <p class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Branch Seal & Sign</p>
                    </div>

                    <!-- Right: Branch Details -->
                    <div class="col-span-5">
                        <div class="bg-blue-50/50 rounded-2xl p-6 border border-blue-100/50 space-y-6">
                            <h3 class="text-xs font-black text-blue-900 uppercase tracking-[0.2em]">Branch Particulars</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-widest">Home Branch Name</label>
                                    <p class="text-sm font-bold text-blue-900"><?= htmlspecialchars($data['branch_name'] ?? 'N/A') ?></p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-widest">IFSC / MICR Code</label>
                                    <p class="text-sm font-bold text-blue-900"><?= htmlspecialchars($data['ifsc_code'] ?? 'N/A') ?></p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-widest">Account Type</label>
                                    <p class="text-sm font-bold text-blue-900">Savings Account (Individual)</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-widest">Assigned Account No.</label>
                                    <p class="text-xl font-black text-blue-900 tracking-tighter"><?= htmlspecialchars($data['account_number'] ?? 'XXXXXXXXXXXX') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-auto border-t border-slate-100 pt-4 flex justify-between items-center text-[9px] text-slate-400 font-bold uppercase tracking-widest">
                <span>Computer Generated Digital Record</span>
                <span>Date: <?= date('d-m-Y H:i:s') ?></span>
                <span>Page 01</span>
            </div>
        </div>

        <!-- PAGE 2: Holder Details -->
        <div class="passbook-page rounded-3xl p-10 flex flex-col relative">
            <div class="watermark uppercase">Confidential</div>
            
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-8">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-[0.3em]">Account Holder Profile</h2>
                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-bold uppercase">KYC Verified</span>
                </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-8">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Primary Account Holder</label>
                        <p class="text-base font-bold text-slate-800 uppercase"><?= htmlspecialchars($data['full_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Registered Phone</label>
                        <p class="text-base font-bold text-slate-800"><?= htmlspecialchars($data['mobile'] ?? 'N/A') ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer ID / Username</label>
                        <p class="text-base font-bold text-slate-800"><?= htmlspecialchars($data['username'] ?? 'N/A') ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account Opening Date</label>
                        <p class="text-base font-bold text-slate-800"><?= date('d F Y', strtotime($data['created_at'])) ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nominee Registered</label>
                        <p class="text-base font-bold text-slate-800 uppercase"><?= htmlspecialchars($data['nominee_name'] ?? 'None Provided') ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email Address</label>
                        <p class="text-base font-bold text-slate-800"><?= htmlspecialchars($data['email'] ?? 'N/A') ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date of Birth</label>
                        <p class="text-base font-bold text-slate-800"><?= htmlspecialchars($data['dob'] ?? 'N/A') ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gender</label>
                        <p class="text-base font-bold text-slate-800"><?= htmlspecialchars($data['gender'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-span-2 space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Communication Address</label>
                        <p class="text-base font-bold text-slate-800 leading-relaxed"><?= htmlspecialchars($data['address'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <div class="mt-auto pt-10 grid grid-cols-3 gap-8">
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center">Holder Signature</p>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center">Bank Verification</p>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center">System Hash</p>
                        <p class="text-[7px] text-slate-300 text-center break-all"><?= hash('sha256', $data['account_number'] . $data['username']) ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-100 pt-4 flex justify-between items-center text-[9px] text-slate-400 font-bold uppercase tracking-widest">
                <span>Secure Digital Passbook Record</span>
                <span>MyBank Systems v2.4</span>
                <span>Page 02</span>
            </div>
        </div>

    </main>

</body>
</html>