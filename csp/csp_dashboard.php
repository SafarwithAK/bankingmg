<?php
session_start();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

if(!isset($_SESSION['csp_id'])){
    header("Location: csp_login.php");
    exit;
}

$pdo = getPDO();
$csp_id = $_SESSION['csp_id'];
$csp_name = $_SESSION['csp_name'];

// 🔹 Fetch CSP info (for branch_id)
$stmt = $pdo->prepare("SELECT * FROM csp_users WHERE id=?");
$stmt->execute([$csp_id]);
$csp = $stmt->fetch();

$users = $pdo->query("
    SELECT 
        u.id,
        u.username,
        u.full_name,
        u.email,
        u.mobile,
        u.address,
        u.dob,
        u.gender,
        u.nominee_name,
        u.kyc_status,
        u.created_at,

        a.account_number,
        a.balance,
        a.currency,
        a.status AS account_status,
        a.created_at AS account_created

    FROM users u
    LEFT JOIN accounts a ON u.id = a.user_id
    ORDER BY u.id DESC
")->fetchAll();


// SEARCH FEATURE
$search = trim($_GET['search'] ?? '');
$search_sql = '';
$params = [$csp['branch_id']]; // filter by branch

if($search){
    $search_sql = " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 🔹 Fetch customers for this CSP branch
$stmt = $pdo->prepare("SELECT * FROM users WHERE branch_id=? $search_sql ORDER BY id DESC");
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Temporary earning placeholder
$earning = 0;
$users = $pdo->query("
    SELECT 
        u.*, 
        a.account_number, 
        a.balance,
        b.ifsc_code,
        b.branch_name
    FROM users u
    LEFT JOIN accounts a ON u.id = a.user_id
    LEFT JOIN branches b ON u.branch_id = b.id
    ORDER BY u.id DESC
")->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>CSP Dashboard | MyBank</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
   body{
    font-family:'Segoe UI';
    background:#f4f6f9;
    margin:0;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    position:fixed;
    top:0;
    left:0;
    height:100vh;
    background:linear-gradient(180deg,#0b3d91,#1e90ff);
    color:white;
    padding-top:20px;
    transition:0.3s;
    z-index:1000;
}

.sidebar.hide{
    left:-260px;
}

.sidebar h3{
    text-align:center;
    font-size:22px;
    margin-bottom:20px;
}

.sidebar a{
    display:block;
    padding:12px 20px;
    color:white;
    text-decoration:none;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.2);
}

/* TOPBAR */
.topbar{
    position:fixed;
    top:0;
    left:250px;
    right:0;
    height:60px;
    background:white;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    transition:0.3s;
}

/* CONTENT */
.content{
    margin-left:260px;
    margin-top:70px;
    padding:20px;
    transition:0.3s;
}

/* CARD */
.card-box{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    margin-bottom:20px;
}

/* MOBILE */
@media(max-width:768px){

    .sidebar{
        left:-260px;
    }

    .sidebar.show{
        left:0;
    }

    .topbar{
        left:0;
    }

    .content{
        margin-left:0;
    }
}

.card-box:hover{
    transform: translateY(-3px);
    transition:0.2s;
}

.content.full{
    margin-left:0 !important;
}

.topbar.full{
    left:0 !important;
}

.sidebar, .content, .topbar{
    transition: all 0.3s ease;
}

@media(max-width:768px){

    .card-box{
        padding:15px;
    }

    .card h5{
        font-size:16px;
    }

    .table{
        font-size:12px;
    }

    .topbar h5{
        font-size:16px;
    }
}


</style>
</head>


<body>


<div class="sidebar">
    <h3> <i class="bi bi-bank2"></i> MYBANK</h3>
<a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
<a onclick="showSection('csp_details')"><i class="bi bi-person-badge"></i> CSP Details</a>
<a onclick="showSection('services')"><i class="bi bi-gear"></i> Services</a>
<a onclick="showSection('customers')"><i class="bi bi-people"></i> Customers</a>
<a onclick="showSection('open_account')"><i class="bi bi-person-plus"></i> Open Customer Account</a>
<a onclick="showSection('change_password')"><i class="bi bi-key"></i> Change Password</a>

<hr>
<a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>
<div class="topbar">

    <button class="btn btn-primary d-md-none" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <h5 class="bi bi-bank2">MyBank CSP Panel</h5>

    <div>
        👤 <?= htmlspecialchars($csp_name) ?>
        <a href="logout.php" class="btn btn-sm btn-danger ms-2">Logout</a>
    </div>

</div>

<div class="content">

<!-- DASHBOARD -->
<div id="dashboard" class="section">
    <div class="row">
        <div class="col-md-4">
            <div class="card-box card-stats text-center">
                <h4>Total Customers</h4>
                <div class="number"><?= count($customers) ?></div>
            </div>
        </div>
        <div class="col-md-4">
    <div class="card-box card-stats text-center">
        <h4>CSP Balance</h4>
        <div class="number">
            ₹ <?= number_format($csp['balance'] ?? 0,2) ?>
        </div>
    </div>
</div>

        <div class="col-md-4">
            <div class="card-box card-stats text-center">
                <h4>CSP Name</h4>
                <div class="number"><?= htmlspecialchars($csp_name) ?></div>
            </div>
        </div>
    </div>
</div>
<?php
// 🔹 Fetch CSP details with branch name
$pdo = getPDO();
$csp_id = $_SESSION['csp_id'];

$stmt = $pdo->prepare("
    SELECT c.*, b.branch_name 
    FROM csp_users c
    LEFT JOIN branches b ON c.branch_id = b.id
    WHERE c.id = ?
");
$stmt->execute([$csp_id]);
$csp = $stmt->fetch();
?>
<div id="csp_details" class="section" style="display:none;">
<div class="card-box">

<h3><i class="bi bi-person-badge"></i> CSP Profile</h3>
<hr>

<div class="row">

    <div class="col-md-4">
        <div class="card p-3 shadow-sm text-center">
            <h5><?= htmlspecialchars($csp['name']) ?></h5>
            <small class="text-muted">CSP Name</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 shadow-sm text-center">
            <h5><?= htmlspecialchars($csp['username']) ?></h5>
            <small class="text-muted">Username</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 shadow-sm text-center">
            <h5>₹ <?= number_format($csp['balance'],2) ?></h5>
            <small class="text-muted">Available Balance</small>
        </div>
    </div>

</div>

<hr>

<div class="row mt-3">

    <div class="col-md-6 mb-3">
        <strong>Email:</strong><br>
        <?= htmlspecialchars($csp['email']) ?>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Mobile:</strong><br>
        <?= htmlspecialchars($csp['mobile']) ?>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Branch:</strong><br>
        <?= htmlspecialchars($csp['branch_name']) ?>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Status:</strong><br>
        <?php if($csp['status']=='Active'): ?>
            <span class="badge bg-success">Active</span>
        <?php else: ?>
            <span class="badge bg-danger">Inactive</span>
        <?php endif; ?>
    </div>

    <div class="col-md-12 mb-3">
        <strong>Address:</strong><br>
        <?= htmlspecialchars($csp['address']) ?>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Joined On:</strong><br>
        <?= date('d M Y', strtotime($csp['created_at'])) ?>
    </div>

</div>

</div>
</div>


<div id="services" class="section" style="display:none;">
<div class="card-box">

<h3>Banking Services</h3>
<hr>

<!-- STEP 1 -->
<div class="mb-3">
    <label>Account Number</label>
    <input type="text" id="account_number" class="form-control">
</div>

<div class="mb-3">
    <label>Select Service</label>
    <select id="service_type" class="form-control" onchange="handleService()">
        <option value="">Select</option>
        <option value="balance">Check Balance</option>
        <option value="deposit">Deposit</option>
        <option value="withdraw">Withdraw</option>
        <option value="statement">Mini Statement</option>
    </select>
</div>
<!-- STEP 2: CHECK BALANCE -->
<div id="balance_section" style="display:none;">
    <h5 class="text-info">Check Balance</h5>
    <button onclick="submitTransaction('balance')" class="btn btn-info w-100">
        Check Balance
    </button>
</div>

<!-- STEP 2: DEPOSIT -->
<div id="deposit_section" style="display:none;">
    <h5 class="text-success">Deposit Money</h5>
    <input type="number" id="deposit_amount" class="form-control mb-2" placeholder="Enter Amount">
    <button onclick="submitTransaction('deposit')" class="btn btn-success w-100">Deposit</button>
</div>

<!-- STEP 2: WITHDRAW -->
<div id="withdraw_section" style="display:none;">
    <h5 class="text-warning">Withdraw Money</h5>
    <input type="number" id="withdraw_amount" class="form-control mb-2" placeholder="Enter Amount">
    <button onclick="submitTransaction('withdraw')" class="btn btn-warning w-100">Withdraw</button>
</div>

<!-- STEP 2: STATEMENT -->
<div id="statement_section" style="display:none;">
    <h5 class="text-primary">Mini Statement</h5>
    <button onclick="submitTransaction('statement')" class="btn btn-primary w-100">View Statement</button>
</div>

<!-- RESULT -->
<div id="result_box" class="mt-4"></div>

</div>
</div>



<?php
// 🔹 Fetch customers + account info for CSP's branch
$pdo = getPDO();
$csp_id = $_SESSION['csp_id'];

// Get CSP branch
$stmt = $pdo->prepare("SELECT branch_id FROM csp_users WHERE id=?");
$stmt->execute([$csp_id]);
$csp = $stmt->fetch();

// SEARCH FEATURE
$search = trim($_POST['search'] ?? '');
$search_sql = '';
$params = [$csp['branch_id']];

if($search){
    $search_sql = " AND (u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Fetch users + account number
$stmt = $pdo->prepare("
    SELECT u.*, a.account_number, a.balance 
    FROM users u
    LEFT JOIN accounts a ON u.id = a.user_id
    WHERE u.branch_id = ? $search_sql
    ORDER BY u.id DESC
");
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>


<!-- CUSTOMER LIST -->
<div id="customers" class="section" style="display:none;">
<div class="card-box">
<h3>Customer List</h3>
<hr>

<input type="text" id="searchCustomer" 
placeholder="Search..." 
onkeyup="searchCustomer()" 
class="form-control mb-3">

<div class="table-responsive" style="display:none;">
<table id="customerTable" class="table table-striped table-bordered text-center" >
            <tr class="table-primary">
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Nominee</th>
                <th>Address</th>
                <th>Contact No</th>
                <th>Account Number</th>
                <th>IFSC Code</th>
                <th>Branch</th>
                <th>KYC Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>

           
            <?php foreach($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['dob'] ?? '-' ?></td>
                <td><?= $u['gender'] ?? '-' ?></td>
                <td><?= $u['nominee_name'] ?? '-' ?></td>
                <td><?= $u['address'] ?? '-' ?></td>

                <td><?= htmlspecialchars($u['mobile']) ?></td>
                <td><?= $u['account_number'] ?? '-' ?></td>
                <td><?= htmlspecialchars($u['ifsc_code'] ?? '-') ?></td>
                <td><?= htmlspecialchars($u['branch_name'] ?? '-') ?></td>
                <td>
                    <?php if($u['kyc_status']=='Verified'): ?>
                        <span class="badge bg-success">Verified</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Not Verified</span>
                    <?php endif; ?>
                </td>

                <td><?= $u['created_at'] ?></td>
                <td class="table-actions">
                    <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmAction('Delete this user?')">Delete</a>
                    <!-- 🔥 KYC ACTION -->
                <br><br>
                <a href="kyc_update.php?id=<?= $u['id'] ?>&status=Verified" 
                   class="btn btn-sm btn-success">Verify</a>

                <a href="kyc_update.php?id=<?= $u['id'] ?>&status=Rejected" 
                   class="btn btn-sm btn-danger">Reject</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

</div>
</div>
</div>

<!-- OPEN CUSTOMER ACCOUNT -->
<div id="open_account" class="section" style="display:none;">
<div class="card-box">
<h3>Open New Customer Account</h3>
<hr>

<?php if(!empty($_SESSION['success_msg'])): ?>
<div class="alert alert-success"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
<?php endif; ?>
<?php if(!empty($_SESSION['error_msg'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
<?php endif; ?>

<form method="post" action="csp_open_account_process.php" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Mobile</label>
        <input type="text" name="mobile" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control"></textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">DOB</label>
        <input type="date" name="dob" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control">
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Nominee Name</label>
        <input type="text" name="nominee_name" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <button class="btn btn-success w-100" name="create_account">Create Account</button>
    </div>
</form>
</div>
</div>

<div id="change_password" class="section" style="display:none;">
<div class="card-box">
<h3>🔐 Change Password</h3>
<hr>
<?php if(isset($_SESSION['success_msg'])): ?>
<script>
    alert(<?= json_encode($_SESSION['success_msg']); ?>);
    window.location.hash = "change_password";
</script>
<?php unset($_SESSION['success_msg']); endif; ?>

<?php if(isset($_SESSION['error_msg'])): ?>
<script>
    alert(<?= json_encode($_SESSION['error_msg']); ?>);
    window.location.hash = "change_password";
</script>
<?php unset($_SESSION['error_msg']); endif; ?>


<form method="POST" action="change_password_process.php">

    <div class="mb-3">
        <label>Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        Update Password
    </button>

</form>

</div>
</div>


</div>
<script>
    
function showSection(id){

    document.querySelectorAll(".section")
        .forEach(sec => sec.style.display = "none");

    document.getElementById(id).style.display = "block";

    // 🔥 SAVE LAST SECTION
    localStorage.setItem("lastSection", id);
}

window.onload = function(){

    let last = localStorage.getItem("lastSection");

    if(last && document.getElementById(last)){
        showSection(last);
    } else {
        showSection("dashboard");
    }
}

function searchCustomer(){

    let input = document.getElementById("searchCustomer").value.trim().toLowerCase();
    let tableWrapper = document.querySelector(".table-responsive");
    let table = document.getElementById("customerTable");
    let rows = table.getElementsByTagName("tr");

    // 🔥 IF EMPTY SEARCH → HIDE FULL TABLE
    if(input === ""){
        tableWrapper.style.display = "none";
        return;
    }

    // SHOW TABLE WHEN SEARCH STARTS
    tableWrapper.style.display = "block";

    let found = false;

    for(let i = 1; i < rows.length; i++){

        let text = rows[i].innerText.toLowerCase();

        if(text.includes(input)){
            rows[i].style.display = "";
            found = true;
        } else {
            rows[i].style.display = "none";
        }
    }

    // If nothing found → hide table
    table.style.display = found ? "table" : "none";
}


// ✅ ONLY ONE handleService
function handleService(){
    let type = document.getElementById("service_type").value;

    document.getElementById("deposit_section").style.display = "none";
    document.getElementById("withdraw_section").style.display = "none";
    document.getElementById("statement_section").style.display = "none";
    document.getElementById("balance_section").style.display = "none";

    if(type === "deposit"){
        document.getElementById("deposit_section").style.display = "block";
    }
    else if(type === "withdraw"){
        document.getElementById("withdraw_section").style.display = "block";
    }
    else if(type === "statement"){
        document.getElementById("statement_section").style.display = "block";
    }
    else if(type === "balance"){
        document.getElementById("balance_section").style.display = "block";
    }
}

// ✅ FINAL API CALL
function submitTransaction(type){

    let acc = document.getElementById("account_number").value.trim();
    let amount = 0;

    // ❗ validation
    if(acc === ""){
        alert("Please enter account number");
        return;
    }

    if(type === "deposit"){
        amount = document.getElementById("deposit_amount").value;
        if(amount <= 0){
            alert("Enter valid amount");
            return;
        }
    }

    if(type === "withdraw"){
        amount = document.getElementById("withdraw_amount").value;
        if(amount <= 0){
            alert("Enter valid amount");
            return;
        }
    }

    fetch("csp_service_api.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            account_number: acc,
            amount: amount,
            type: type
        })
    })
    .then(res => res.json())
    .then(data => {

    if(data.status === "success"){

        if(type === "statement"){
            let html = "<h5>Mini Statement</h5><table class='table table-bordered'>";
            html += "<tr><th>Type</th><th>Amount</th><th>Description</th><th>From Account</th><th>To Account</th><th>Date</th></tr>";

            data.data.forEach(t=>{
                html += `<tr>
    <td>${t.type}</td>
    <td>₹${t.amount}</td>
    <td>${t.description}</td>
    <td>${t.from_account ?? '-'}</td>
    <td>${t.to_account ?? '-'}</td>
    <td>${t.date}</td>
</tr>`;

            });

            html += "</table>";
            document.getElementById("result_box").innerHTML = html;
        }else if(type === "balance"){
            document.getElementById("result_box").innerHTML =
                `<div class='alert alert-info'>Balance: ₹ ${data.balance}</div>`;
        }else{
            document.getElementById("result_box").innerHTML =
                "<div class='alert alert-success'>" + data.message + "</div>";
        }

        // 🔥 RESET UI AFTER SUCCESS
        resetServiceForm();

    }else{
        document.getElementById("result_box").innerHTML =
            "<div class='alert alert-danger'>" + data.message + "</div>";
    }
});

}

function resetServiceForm(){

    // Clear inputs
    document.getElementById("account_number").value = "";
    document.getElementById("deposit_amount").value = "";
    document.getElementById("withdraw_amount").value = "";

    // Reset dropdown
    document.getElementById("service_type").value = "";

    // Hide all sections
    document.getElementById("deposit_section").style.display = "none";
    document.getElementById("withdraw_section").style.display = "none";
    document.getElementById("statement_section").style.display = "none";
    document.getElementById("balance_section").style.display = "none";
}

setTimeout(() => {
    resetServiceForm();
}, 2000);

</script>


</body>
</html>
