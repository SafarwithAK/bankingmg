<?php

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';


if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit; }
$pdo = getPDO();


// DASHBOARD STATS
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAccounts = $pdo->query("SELECT COUNT(*) FROM accounts")->fetchColumn();

// 🔥 ADMIN DATA FETCH
$stmt = $pdo->prepare("SELECT username, email FROM admins WHERE id=?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// 🔥 NAME DISPLAY (username > email)
$adminName = $admin['username'] ? $admin['username'] : $admin['email'];


// USERS DATA
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

// ACCOUNTS DATA WITH STATUS
$accounts = $pdo->query("SELECT a.id, a.account_number, a.balance, a.status, u.username 
                         FROM accounts a 
                         JOIN users u ON a.user_id = u.id 
                         ORDER BY a.id DESC")->fetchAll();

 // FETCH BRANCHES
        $branches = $pdo->query("SELECT * FROM branches ORDER BY id DESC")->fetchAll();
// FETCH BRANCHES FOR DROPDOWN
$branchList = $pdo->query("SELECT id, branch_name FROM branches")->fetchAll();

// FETCH CSP USERS
$cspUsers = $pdo->query("
    SELECT c.*, b.branch_name 
    FROM csp_users c
    JOIN branches b ON c.branch_id = b.id
    ORDER BY c.id DESC
")->fetchAll();

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
<html>
<head>
<title>Admin Panel - MyBank</title>
<meta charset="utf-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{
    font-family:'Segoe UI'; 
    background:#f4f6f9;
}
.sidebar{
    width:250px;
    position:fixed; 
    top:0; left:0; 
    height:100vh; 
    background:linear-gradient(180deg,#0b3d91,#1e90ff); 
    color:white; 
    padding-top:20px;
}
.sidebar h3{
    text-align:center; 
    font-size:24px; 
    font-weight:bold; margin-bottom:30px;}
.sidebar a{
    display:block; 
    padding:14px 20px; 
    color:white; 
    text-decoration:none; 
    border-left:4px solid transparent; 
    transition:0.3s; 
    font-size:17px;
}
.sidebar a:hover, .sidebar a.active{
    background:rgba(255,255,255,0.15); 
    border-left:4px solid #fff;
}
.content{
    margin-left:260px; 
    padding:25px;
}
.card-box{
    background:white; 
    padding:20px; 
    border-radius:12px; 
    box-shadow:0 6px 15px rgba(0,0,0,0.1); 
    margin-bottom:20px;
}
.card-stats h4{
    font-weight:bold; 
    font-size:20px;
}
.card-stats .number{
    font-size:28px; 
    font-weight:bold; 
    color:#0d6efd;
}

.table-actions button, .table-actions a{
    margin-right:5px;
}
.topbar{
    position:fixed;
    top:0;
    left:250px;
    right:0;
    height:60px;
    background:white;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 20px;
    z-index:1000;
}

.topbar h5{
    margin:0;
    font-weight:bold;
    text-align:center;
}

.topbar .admin-info{
    font-size:14px;
    color:#555;
}

.content{
    margin-left:260px;
    padding:25px;
    margin-top:70px; /* 🔥 IMPORTANT */
}

</style>
</head>
<body>

<div class="sidebar">
<h3><i class="bi bi-bank2"></i> MYBANK</h3>
<a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
<a onclick="showSection('branches')"><i class="bi bi-building"></i> Branches</a>
<a onclick="showSection('csp')"><i class="bi bi-person-badge"></i> CSP Users</a>
<a onclick="showSection('users')"><i class="bi bi-people"></i> Users</a>
<a onclick="showSection('accounts')"><i class="bi bi-wallet2"></i> Accounts</a>
<a onclick="showSection('changePassword')"><i class="bi bi-key"></i> Change Password</a>

<hr>
<a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>
<!-- TOP HEADER -->
 
<div class="topbar">
    <a href="admin_dashboard.php" class="text-decoration-none text-dark">
         <h5><i class="bi bi-bank2"></i> MyBank Admin Panel</h5>
    </a>
     <div class="admin-info">
        👤 <?= htmlspecialchars($adminName) ?> 
        <a href="logout.php" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>


<div class="content">
<!-- DASHBOARD -->
<div id="dashboard" class="section">
    <div class="row">
        <div class="col-md-4">
            <div class="card-box card-stats text-center">
                <h4>Total Users</h4>
                <div class="number"><?= $totalUsers ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-box card-stats text-center">
                <h4>Total Accounts</h4>
                <div class="number"><?= $totalAccounts ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-box card-stats text-center">
                <h4>Total Branches</h4>
                <div class="number"><?= count($branches) ?></div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card-box card-stats text-center">
                <h4>Total CSP Users</h4>
                <div class="number"><?= count($cspUsers) ?></div>
            </div>
        </div>

    </div>
</div>
<!-- BRANCH MANAGEMENT -->
<div id="branches" class="section" style="display:none;">
    <div class="card-box">
        <h3><i class="bi bi-building"></i> Branch Management</h3>
        <hr>

        <!-- ADD FORM -->
        <form method="post" action="add_branch.php" class="row g-3 mb-4">

            <div class="col-md-3">
                <input name="branch_name" class="form-control" placeholder="Branch Name" required>
            </div>

            <div class="col-md-3">
                <input name="ifsc_code" class="form-control" placeholder="IFSC Code (MYBK0001234)" required>
            </div>

            <div class="col-md-3">
                <input name="city" class="form-control" placeholder="City" required>
            </div>

            <div class="col-md-3">
                <input name="state" class="form-control" placeholder="State" required>
            </div>

            <div class="col-md-3">
                <input name="address" class="form-control" placeholder="Full Address">
            </div>

            <div class="col-md-12">
                <button name="add_branch" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Add Branch
                </button>
            </div>

        </form>

        <!-- TABLE -->
        <div class="table-responsive">
        <table class="table table-bordered table-striped text-center">
            <tr class="table-primary">
                <th>ID</th>
                <th>Branch</th>
                <th>IFSC</th>
                <th>City</th>
                <th>State</th>
                <th>Address</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>

            <?php foreach($branches as $b): ?>
<tr>
    <td><?= $b['id'] ?></td>
    <td><?= htmlspecialchars($b['branch_name']) ?></td>
    <td><b><?= $b['ifsc_code'] ?></b></td>
    <td><?= htmlspecialchars($b['city']) ?></td>
    <td><?= htmlspecialchars($b['state']) ?></td>
    <td><?= htmlspecialchars($b['address']) ?></td>
    <td><?= $b['created_at'] ?></td>
    <td><a href="delete_branch.php?id=<?= $b['id'] ?>"class="btn btn-sm btn-danger"onclick="return confirm('Delete this branch?')"><i class="bi bi-trash"></i></a>
        <a href="edit_branch.php?id=<?= $b['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

    </td>

</tr>
<?php endforeach; ?>


        </table>
        </div>

    </div>
</div>


<!-- CSP MANAGEMENT -->
<div id="csp" class="section" style="display:none;">
    <div class="card-box">
        <h3><i class="bi bi-person-badge"></i> CSP Management</h3>
        <hr>
<!-- ADD CSP FORM -->
<form method="post" action="add_csp.php" class="row g-3 mb-4">

    <div class="col-md-4">
        <input name="name" class="form-control" placeholder="CSP Name" required>
    </div>

    <div class="col-md-4">
        <input name="email" type="email" class="form-control" placeholder="Email">
    </div>

    <div class="col-md-4">
        <input name="mobile" class="form-control" placeholder="Mobile">
    </div>

 
    <div class="col-md-4">
        <select name="branch_id" class="form-control" required>
            <option value="">Select Branch</option>
            <?php foreach($branchList as $b): ?>
                <option value="<?= $b['id'] ?>">
                    <?= htmlspecialchars($b['branch_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
     </div>
     
     <div class="col-md-4">
         <input name="address" class="form-control" placeholder="Full Address">
     </div>

    <div class="col-md-4">
        <input name="password" type="password" class="form-control" placeholder="Password" required>
    </div>

    <div class="col-md-12">
        <button name="add_csp" class="btn btn-success w-100">
            <i class="bi bi-plus-circle"></i> Add CSP
        </button>
    </div>

</form>

<!-- CSP TABLE -->
<div class="table-responsive">
<table class="table table-bordered text-center">
    <tr class="table-primary">
        <th>ID</th>
        <th>Name</th>
        <th>Branch</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Address</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

<?php foreach($cspUsers as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= htmlspecialchars($c['name']) ?></td>
    <td><?= htmlspecialchars($c['branch_name']) ?></td>
    <td><?= $c['email'] ?></td>
    <td><?= $c['mobile'] ?></td>
    <td><?= $c['address'] ?></td>
    <td>
        <?php if($c['status']=='Active'): ?>
            <span class="badge bg-success">Active</span>
        <?php else: ?>
            <span class="badge bg-secondary">Inactive</span>
        <?php endif; ?>
    </td>
    <td>
        <a href="delete_csp.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger"onclick="return confirm('Delete CSP?')"><i class="bi bi-trash"></i></a>
        <a href="edit_csp.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

    </td>
</tr>
<?php endforeach; ?>

</table>
</div>

    </div>
</div>

<!-- USERS MANAGEMENT -->
<div id="users" class="section" style="display:none;">
    <div class="table-responsive">
        <h3>Users</h3>
        <hr>
        <table class="table table-striped table-bordered text-center">
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

<!-- ACCOUNTS MANAGEMENT -->
<div id="accounts" class="section" style="display:none;">
    <div class="table-responsive">
        <h3>Accounts</h3>
        <hr>
        <table class="table table-striped table-bordered text-center">
            <tr class="table-primary">
                <th>ID</th>
                <th>User</th>
                <th>Account Number</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach($accounts as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['username']) ?></td>
                <td><?= $a['account_number'] ?></td>
                <td>₹ <?= number_format($a['balance'],2) ?></td>
                <td>
                    <?php if($a['status']=='Active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td class="table-actions">
                    <a href="toggle_account.php?id=<?= $a['id'] ?>"class="btn btn-sm btn-info" 
                    onclick="return confirmAction('Are you sure you want to <?= $a['status']=='Active' ? 'deactivate' : 'activate' ?> this account?')">
                    <?= $a['status']=='Active' ? 'Deactivate' : 'Activate' ?>
                    </a>
                    <a href="delete_account.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmAction('Delete this account?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>


<!-- CHANGE PASSWORD -->
<div id="changePassword" class="section" style="display:none;">
    <div class="card-box">
        <h3>Change Password</h3>
        <hr>
        <form method="post" action="change_password.php" class="mb-3">
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

            <button type="submit" name="change_pass" class="btn btn-warning w-100">
                <i class="bi bi-key"></i> Change Password
            </button>

        </form>

    </div>
</div>


</div>
</body>

<script>
function showSection(id){
    document.querySelectorAll(".section").forEach(sec=>sec.style.display='none');
    document.getElementById(id).style.display='block';
    document.querySelectorAll(".sidebar a").forEach(a=>a.classList.remove('active'));
    document.querySelector("[onclick=\"showSection('"+id+"')\"]").classList.add('active');
}
function confirmAction(msg){ return confirm(msg); }

function showSection(id){
    document.querySelectorAll(".section").forEach(sec=>sec.style.display='none');
    document.getElementById(id).style.display='block';

    document.querySelectorAll(".sidebar a").forEach(a=>a.classList.remove('active'));
    document.querySelector("[onclick=\"showSection('"+id+"')\"]").classList.add('active');

    // 🔥 title change
    document.getElementById("pageTitle").innerText = id.toUpperCase();
}
</script>
</html>
