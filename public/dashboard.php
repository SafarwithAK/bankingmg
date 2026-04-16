<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
requireLogin();

$pdo = getPDO();




// Fetch user + branch + account details in **single query**
$stmt = $pdo->prepare("
    SELECT 
        u.*, 
        b.branch_name, 
        b.ifsc_code,
        a.account_number,
        a.balance
    FROM users u
    LEFT JOIN branches b ON u.branch_id = b.id
    LEFT JOIN accounts a ON u.id = a.user_id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if branch info exists
if(!$user['branch_name']) {
    $user['branch_name'] = 'Main Branch';
}
if(!$user['ifsc_code']) {
    $user['ifsc_code'] = 'MYBK0001234';
}
// ACCOUNT DETAILS
$stmt = $pdo->prepare("SELECT id, account_number, balance FROM accounts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch();
$txnStmt = $pdo->prepare("
    SELECT 
        t.id,
        t.amount,
        t.type,
        t.description,
        t.created_at,
        t.from_account_id,
        t.to_account_id,

        a_from.account_number AS from_account_number,
        u_from.full_name AS from_name,

        a_to.account_number AS to_account_number,
        u_to.full_name AS to_name

    FROM transactions t

    LEFT JOIN accounts a_from ON t.from_account_id = a_from.id
    LEFT JOIN users u_from ON a_from.user_id = u_from.id

    LEFT JOIN accounts a_to ON t.to_account_id = a_to.id
    LEFT JOIN users u_to ON a_to.user_id = u_to.id

    WHERE t.from_account_id = ? OR t.to_account_id = ?
    ORDER BY t.created_at DESC
    LIMIT 20
");

$txnStmt->execute([$account['id'], $account['id']]);
$transactions = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

// ================= CHANGE PASSWORD =================
if(isset($_POST['change_password'])){

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if($new !== $confirm){
        $_SESSION['pass_msg'] = "<div class='alert alert-danger'>Passwords do not match!</div>";
    }
    elseif(strlen($new) < 6){
        $_SESSION['pass_msg'] = "<div class='alert alert-danger'>Password must be at least 6 characters!</div>";
    }
    else{

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();

        if($userData && password_verify($current, $userData['password_hash'])){

            $newHash = password_hash($new, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $stmt->execute([$newHash, $_SESSION['user_id']]);

            $_SESSION['pass_msg'] = "<div class='alert alert-success'>Password changed successfully!</div>";

        } else {
            $_SESSION['pass_msg'] = "<div class='alert alert-danger'>Current password is incorrect!</div>";
        }
    }

    header("Location: ".$_SERVER['PHP_SELF']."#changePassword");
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - MyBank</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: #e6f0ff; /* subtle banking blue */
    font-family: 'Segoe UI', sans-serif;
    margin:0;
}

/* Sidebar */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top:0;
    left:0;
    background: linear-gradient(180deg, #0b3d91, #1e90ff);
    color:white;
    padding-top: 20px;
    font-weight: 500;
    transition: 0.3s;
}
.sidebar h3 {
    font-size: 26px;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 30px;
    text-align:center;
    letter-spacing: 1px;
}
.sidebar a {
    display: block;
    padding: 12px 20px;
    color:white;
    text-decoration:none;
    font-size: 16px;
    border-left: 4px solid transparent;
    transition: all 0.3s;
    border-radius: 0 10px 10px 0;
}
.sidebar a:hover {
    background: rgba(255,255,255,0.15);
    border-left: 4px solid #fff;
}

/* Content */
.content {
    margin-left: 260px;
    padding: 20px 30px;
}

/* Card */
.card-box {
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

/* Balance text */
.big-balance {
    font-size: 28px;
    font-weight: bold;
    color: #0d6efd;
}

/* Buttons */
.btn-gradient {
    background: linear-gradient(90deg,#0d6efd,#2980ff);
    color:white;
    border:none;
    border-radius:8px;
    font-weight:600;
    transition: all 0.3s;
}
.btn-gradient:hover {
    opacity:0.9;
    transform: translateY(-1px);
}

/* Transaction badges */
.badge-credit { background-color: #198754; font-size: 0.85rem; padding:0.35em 0.6em; }
.badge-debit { background-color: #dc3545; font-size: 0.85rem; padding:0.35em 0.6em; }

/* Table adjustments */
.table th, .table td { vertical-align: middle; }
.table-hover tbody tr:hover { background: rgba(13,110,253,0.05); }

/* Responsive */
@media(max-width:768px){
    .sidebar { width:100%; height:auto; position:relative; }
    .content { margin-left:0; padding:15px; }
}

/* Section headers */
.section h3 {
    font-size: 22px;
    font-weight: 600;
    color: #0b3d91;
}

/* Small icons in card headers */
.section i.bi {
    margin-right: 8px;
    color: #0d6efd;
}

/* Alerts */
.alert {
    border-radius: 10px;
    font-size: 0.95rem;
}

.topbar {
    position: fixed;
    top: 0;
    left: 260px; /* sidebar ke baad */
    right: 0;
    height: 60px;
    background: #fff;
    border-bottom: 1px solid #ddd;
    z-index: 1000;
}

/* Content ko niche push karo */
.content {
    margin-left: 260px;
    padding: 90px 30px 20px; /* top padding = header space */
}


.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #0b3d91, #1e90ff);
    color: white;
    padding-top: 20px;
    transition: 0.3s;
    z-index: 1001;
}

/* MOBILE MODE */
@media(max-width:768px){
    .sidebar {
        left: -260px; /* hide */
    }

    .sidebar.active {
        left: 0; /* show */
    }
}

/* Desktop */
.content {
    margin-left: 260px;
    padding: 90px 30px 20px;
}

.topbar {
    position: fixed;
    top: 0;
    left: 260px;
    right: 0;
    height: 60px;
    background: #fff;
    border-bottom: 1px solid #ddd;
    z-index: 1000;
}

/* Mobile */
@media(max-width:768px){
    .content {
        margin-left: 0;
        padding: 80px 15px;
    }

    .topbar {
        left: 0;
    }
}


@media(max-width:768px){
    .topbar {
        left: 0;
    }
}


</style>


</head>
<body>

<div class="sidebar">
    <h3><i class="bi bi-bank2"></i> MyBank</h3>
    <a onclick="showSection('dashboard')"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a onclick="showSection('details')"><i class="bi bi-person-vcard"></i> Account Holder</a>
    <a href="passbook.php" target="_blank"><i class="bi bi-file-earmark-text"></i> View Passbook</a>
    <a onclick="showSection('balance')"><i class="bi bi-wallet2"></i> Check Balance</a>
    <a onclick="showSection('transfer')"><i class="bi bi-currency-rupee"></i> Money Transfer</a>
    <a onclick="showSection('addMoney')"><i class="bi bi-wallet-plus"></i> Add Money</a>
    <a onclick="showSection('history')"><i class="bi bi-clock-history"></i> Transaction History</a>
    <a onclick="showSection('changePassword')"> <i class="bi bi-key"></i> Change Password</a>

    <hr>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="topbar d-flex justify-content-between align-items-center px-4 py-2 shadow-sm">

    <!-- LEFT -->
    <div class="d-flex align-items-center gap-2">
        <!-- HAMBURGER -->
        <i class="bi bi-list fs-3 d-md-none" onclick="toggleSidebar()" style="cursor:pointer;"></i>

        <h5 class="mb-0 text-primary">
            <i class="bi bi-bank2"></i> MyBank USER Panel
        </h5>
    </div>

    <!-- RIGHT -->
    <div class="user-section d-flex align-items-center">
        <span class="me-3 fw-semibold">
            👤 <?= htmlspecialchars($user['username'] ?? 'User') ?>
        </span>

        <a href="logout.php" class="btn btn-sm btn-danger">
            Logout
        </a>
    </div>

</div>


<div class="content">

<!-- DASHBOARD -->
<div id="dashboard" class="section">
    <div class="card-box">
        <h3>Welcome, <?= htmlspecialchars($user['username']) ?></h3>
        <p>Last Login: <?= htmlspecialchars($user['created_at']) ?></p>
        <hr>
        <h4>Account Summary</h4>
        <p><b>Account Number:</b> <?= $account['account_number'] ?></p>
        <p><b>Available Balance:</b> <span class="big-balance">₹ <?= number_format($account['balance'],2) ?></span></p>
    </div>
</div>
<!-- ACCOUNT DETAILS (PASSBOOK STYLE CLEAN) -->
<div id="details" class="section" style="display:none;">
    <div class="card-box">

        <h3 class="text-center mb-3">
            <i class="bi bi-book"></i> Account Passbook Details
        </h3>

        <hr>

        <div style="
            border:2px solid #0d6efd;
            border-radius:14px;
            padding:20px;
            background:linear-gradient(180deg,#f9fbff,#ffffff);
        ">

            <!-- BANK HEADER -->
            <div class="text-center mb-4">
                <h2 style="
                    color:#0d6efd;
                    font-weight:900;
                    font-size:38px;
                    letter-spacing:3px;
                    text-shadow:0 3px 8px rgba(0,0,0,0.2);
                ">
                    🏦 MY BANK
                </h2>

                <div style="font-size:14px; color:#444;">
                    <b>Branch:</b> <?= htmlspecialchars($user['branch_name'] ?? 'N/A') ?> |
                    <b>IFSC:</b> <?= htmlspecialchars($user['ifsc_code'] ?? 'N/A') ?>
                </div>
            </div>

            <!-- ACCOUNT INFO -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <b>Account Holder:</b><br>
                    <?= htmlspecialchars($user['full_name']) ?>
                </div>

                <div class="col-md-6">
                    <b>Account Number:</b><br>
                    <?= htmlspecialchars($user['account_number']) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <b>Username:</b><br>
                    <?= htmlspecialchars($user['username']) ?>
                </div>

                <div class="col-md-6">
                    <b>Email:</b><br>
                    <?= htmlspecialchars($user['email']) ?>
                </div>
            </div>

            <!-- PERSONAL INFO -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <b>DOB:</b><br>
                    <?= htmlspecialchars($user['dob']) ?>
                </div>

                <div class="col-md-4">
                    <b>Gender:</b><br>
                    <?= htmlspecialchars($user['gender']) ?>
                </div>

                <div class="col-md-4">
                    <b>Contact:</b><br>
                    <?= htmlspecialchars($user['mobile'] ?? 'N/A') ?>
                </div>
            </div>

            <!-- ADDRESS -->
            <div class="mb-3">
                <b>Address:</b><br>
                <?= htmlspecialchars($user['address']) ?>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <b>Nominee:</b><br>
                    <?= htmlspecialchars($user['nominee_name'] ?? 'N/A') ?>
                </div>

                <div class="col-md-6">
                    <b>KYC Status:</b><br>
                    <?php if($user['kyc_status'] == 'Verified'): ?>
                        <span style="color:green; font-weight:bold;">✔ Verified</span>
                    <?php else: ?>
                        <span style="color:orange; font-weight:bold;">⏳ Pending</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="text-end mt-3">
                <small>
                    Account Opened: <?= date('d M Y', strtotime($user['created_at'])) ?>
                </small>
            </div>

        </div>

    </div>
</div>


<!-- BALANCE -->
<div id="balance" class="section" style="display:none;">
    <div class="card-box text-center">
        <h3>Available Balance</h3>
        <hr>
        <p class="big-balance">₹ <?= number_format($account['balance'],2) ?></p>
    </div>
</div>


<!-- MONEY TRANSFER -->
<div id="transfer" class="section" style="display:none;">
    <div class="card-box">
        <h3><i class="bi bi-currency-exchange"></i> Send Money</h3>
        <p class="text-muted">Secure Instant Transfer</p>
        <hr>

        <!-- Current Balance Toggle -->
        <p class="fw-semibold">
            Current Balance: 
            <span id="currentBalance" class="fw-bold">₹ <?= number_format($account['balance'],2) ?></span>
            <i id="toggleBalance" class="bi bi-eye-fill ms-2" style="cursor:pointer; color:#0d6efd;" title="Show/Hide Balance"></i>
        </p>

        <?php if(!empty($_SESSION['transfer_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <?= $_SESSION['transfer_success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['transfer_success']); ?>
        <?php endif; ?>

        <?php if(!empty($_SESSION['transfer_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <?= $_SESSION['transfer_error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['transfer_error']); ?>
        <?php endif; ?>

        <form action="transfer.php" method="post" autocomplete="off" class="mt-4">
            <div class="mb-3">
                <label class="form-label fw-semibold">Receiver Account Number</label>
                <input name="to_account" class="form-control form-control-lg" placeholder="Enter receiver account number" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Amount (₹)</label>
                <input name="amount" type="number" class="form-control form-control-lg" placeholder="Enter amount" min="1" step="0.01" required>
            </div>
            <button class="btn btn-gradient w-100"><i class="bi bi-send-fill"></i> Send Money</button>
        </form>
    </div>
</div>

<script>
    // Toggle balance visibility
    const toggleBtn = document.getElementById('toggleBalance');
    const balanceText = document.getElementById('currentBalance');

    toggleBtn.addEventListener('click', () => {
        if(balanceText.innerText.startsWith('₹')) {
            // Hide balance
            balanceText.innerText = '*****';
            toggleBtn.classList.replace('bi-eye-fill','bi-eye-slash-fill');
        } else {
            // Show balance
            balanceText.innerText = '₹ <?= number_format($account['balance'],2) ?>';
            toggleBtn.classList.replace('bi-eye-slash-fill','bi-eye-fill');
        }
    });
</script>

<!-- ADD MONEY -->
<div id="addMoney" class="section" style="display:none;">
    <div class="card-box">
        <h3><i class="bi bi-wallet-plus"></i> Add Money</h3>
        <p class="text-muted">Instantly add funds to your account</p>
        <hr>

        <!-- Current Balance Toggle -->
        <p class="fw-semibold">
            Current Balance: 
            <span id="currentBalanceAdd" class="fw-bold">₹ <?= number_format($account['balance'],2) ?></span>
            <i id="toggleBalanceAdd" class="bi bi-eye-fill ms-2" style="cursor:pointer; color:#0d6efd;" title="Show/Hide Balance"></i>
        </p>

        <!-- Success/Error Messages -->
        <?php if(!empty($_SESSION['add_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <?= $_SESSION['add_success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['add_success']); ?>
        <?php endif; ?>

        <?php if(!empty($_SESSION['add_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <?= $_SESSION['add_error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['add_error']); ?>
        <?php endif; ?>

        <!-- Add Money Form -->
        <form action="payment_options.php" method="post" autocomplete="off" class="mt-4">
            <div class="mb-3">
                <label class="form-label fw-semibold">Amount (₹)</label>
                <input name="amount" type="number" class="form-control form-control-lg" placeholder="Enter amount" min="1" step="0.01" required>
            </div>
            <button class="btn btn-gradient w-100"><i class="bi bi-plus-circle-fill"></i> Add Money</button>
        </form>
    </div>
</div>

<script>
    // Toggle balance visibility for Add Money
    const toggleBtnAdd = document.getElementById('toggleBalanceAdd');
    const balanceTextAdd = document.getElementById('currentBalanceAdd');

    toggleBtnAdd.addEventListener('click', () => {
        if(balanceTextAdd.innerText.startsWith('₹')) {
            balanceTextAdd.innerText = '*****';
            toggleBtnAdd.classList.replace('bi-eye-fill','bi-eye-slash-fill');
        } else {
            balanceTextAdd.innerText = '₹ <?= number_format($account['balance'],2) ?>';
            toggleBtnAdd.classList.replace('bi-eye-slash-fill','bi-eye-fill');
        }
    });
</script>

<!-- TRANSACTION HISTORY -->
<div id="history" class="section" style="display:none;">
    <div class="card-box">

        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-clock-history" style="font-size:28px; color:#0d6efd;"></i>
            <h3 class="m-0">Transaction History</h3>
        </div>

        <p class="text-muted mb-4">Latest 20 transactions</p>
        <hr>

        <?php
        $accountId = $account['id'] ?? 0;

        function txnBadge($type){
            if($type === 'deposit') return '<span class="badge bg-success">Received</span>';
            if($type === 'withdraw') return '<span class="badge bg-danger">Withdraw</span>';
            return '<span class="badge bg-primary">Transfer</span>';
        }

        // ======================
        // CREDIT (MONEY RECEIVED)
        // ======================
        $credits = array_filter($transactions, function($t) use ($accountId){
            return $t['to_account_id'] == $accountId;
        });
        ?>

        <h5 class="text-success mb-3">
            <i class="bi bi-arrow-down-left-circle"></i> Money Received
        </h5>

        <?php if(!empty($credits)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>From Account</th>
                        <th>Sender Name</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($credits as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>

                        <td class="text-success fw-bold">
                            + ₹ <?= number_format($t['amount'],2) ?>
                        </td>

                        <td>
                            <?= $t['from_account_number'] ?? 'System' ?>
                        </td>

                        <td>
                            <?= $t['from_name'] ?? 'Bank' ?>
                        </td>

                        <td>
                            <?= date('d M Y, H:i', strtotime($t['created_at'])) ?>
                        </td>

                        <td><?= txnBadge($t['type']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-light border text-center">No Money Received</div>
        <?php endif; ?>


        <?php
        // ======================
        // DEBIT (MONEY SENT)
        // ======================
        $debits = array_filter($transactions, function($t) use ($accountId){
            return $t['from_account_id'] == $accountId;
        });
        ?>

        <h5 class="text-danger mt-4 mb-3">
            <i class="bi bi-arrow-up-right-circle"></i> Money Sent
        </h5>

        <?php if(!empty($debits)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle text-center">
                <thead class="table-danger">
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>To Account</th>
                        <th>Receiver Name</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($debits as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>

                        <td class="text-danger fw-bold">
                            - ₹ <?= number_format($t['amount'],2) ?>
                        </td>

                        <td>
                            <?= $t['to_account_number'] ?? 'System' ?>
                        </td>

                        <td>
                            <?= $t['to_name'] ?? 'Bank' ?>
                        </td>

                        <td>
                            <?= date('d M Y, H:i', strtotime($t['created_at'])) ?>
                        </td>

                        <td><?= txnBadge($t['type']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-light border text-center">No Money Sent</div>
        <?php endif; ?>

    </div>
</div>


<!-- CHANGE PASSWORD -->
<div id="changePassword" class="section" style="display:none;">
    <div class="card-box">
        <h3><i class="bi bi-key"></i> Change Password</h3>
        <p class="text-muted">Update your account password securely</p>
        <hr>

        <!-- ONE TIME ALERT -->
        <?php
        if(!empty($_SESSION['pass_msg'])){
            echo $_SESSION['pass_msg'];
            unset($_SESSION['pass_msg']);
        }
        ?>

        <form method="post" class="mt-3">

            <div class="mb-3">
                <label class="form-label fw-semibold">Current Password</label>
                <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
            </div>

            <button type="submit" name="change_password" class="btn btn-gradient w-100">
                <i class="bi bi-shield-lock"></i> Update Password
            </button>

        </form>
    </div>
</div>



<script>
    function showSection(id){
    document.querySelectorAll(".section").forEach(sec => sec.style.display = "none");
    document.getElementById(id).style.display = "block";

    // save last opened section
    localStorage.setItem("activeSection", id);
}

window.onload = function(){
    let last = localStorage.getItem("activeSection");

    if(last && document.getElementById(last)){
        showSection(last);
    } else {
        showSection("dashboard");
    }
}

document.querySelectorAll(".sidebar a").forEach(a => {
    a.addEventListener("click", function(){
        document.querySelectorAll(".sidebar a").forEach(x => x.classList.remove("active"));
        this.classList.add("active");
    });
});

function toggleSidebar(){
    document.querySelector(".sidebar").classList.toggle("active");
}

document.addEventListener("click", function(e){
    let sidebar = document.querySelector(".sidebar");

    if(window.innerWidth <= 768){
        if(!sidebar.contains(e.target) && !e.target.classList.contains("bi-list")){
            sidebar.classList.remove("active");
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
