<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$pdo = getPDO();

$id = $_GET['id'] ?? null;

// 🔹 FETCH CSP
$stmt = $pdo->prepare("SELECT * FROM csp_users WHERE id=?");
$stmt->execute([$id]);
$csp = $stmt->fetch();

if(!$csp){
    die("CSP not found!");
}

// 🔹 UPDATE
if(isset($_POST['update_csp'])){

    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];

    if(strlen($name) < 3){
        $_SESSION['msg'] = "<div class='alert alert-danger'>Name too short!</div>";
    }
    else{

        $stmt = $pdo->prepare("
            UPDATE csp_users 
            SET name=?, username=?, email=?, mobile=?, address=?, status=? 
            WHERE id=?
        ");
        $stmt->execute([$name, $username, $email, $mobile, $address, $status, $id]);

        $_SESSION['msg'] = "<div class='alert alert-success'>✅ CSP updated successfully!</div>";
    }

    header("Location: edit_csp.php?id=".$id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit CSP | MyBank</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    margin:0;
    background: linear-gradient(135deg,#0b3d91,#1e90ff);
    font-family:'Segoe UI';
}

/* NAVBAR */
.navbar-custom{
    background: rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
}

/* CARD */
.card-box{
    max-width:650px;
    margin:30px auto;
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

/* TITLE */
.card-box h4{
    font-weight:bold;
    color:#0b3d91;
}

/* INPUT */
.form-control{
    border-radius:8px;
}

/* BUTTON */
.btn-primary{
    background:linear-gradient(90deg,#0d6efd,#2980ff);
    border:none;
}
.btn-primary:hover{
    opacity:0.9;
}

</style>
</head>

<body>

<!-- 🔥 NAVBAR -->
<nav class="navbar navbar-dark navbar-custom px-3">
    <span class="navbar-brand fw-bold text-white">
        <i class="bi bi-bank2"></i> MyBank - Edit CSP
    </span>

    <div>
        <a href="admin_dashboard.php#csp" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        <a href="logout.php" class="btn btn-danger btn-sm ms-2">
            Logout
        </a>
    </div>
</nav>

<!-- 🔥 FORM CARD -->
<div class="card-box">

    <h4><i class="bi bi-person-badge"></i> Edit CSP</h4>
    <hr>

    <!-- ALERT -->
    <?php
    if(isset($_SESSION['msg'])){
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
    ?>

    <form method="post" class="row g-3">

        <div class="col-md-12">
            <label>Name</label>
            <input name="name"
                   value="<?= htmlspecialchars($csp['name']) ?>"
                   class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Username</label>
            <input name="username"
                   value="<?= htmlspecialchars($csp['username']) ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <label>Email</label>
            <input name="email"
                   value="<?= htmlspecialchars($csp['email']) ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <label>Mobile</label>
            <input name="mobile"
                   value="<?= htmlspecialchars($csp['mobile']) ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="Active" <?= $csp['status']=='Active'?'selected':'' ?>>Active</option>
                <option value="Inactive" <?= $csp['status']=='Inactive'?'selected':'' ?>>Inactive</option>
            </select>
        </div>

        <div class="col-md-12">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= htmlspecialchars($csp['address']) ?></textarea>
        </div>

        <div class="col-md-6">
            <button name="update_csp" class="btn btn-primary w-100">
                <i class="bi bi-save"></i> Update CSP
            </button>
        </div>

        <div class="col-md-6">
            <a href="admin_dashboard.php#csp" class="btn btn-secondary w-100">
                Cancel
            </a>
        </div>

    </form>

</div>

<script>
setTimeout(()=>{
    let alert = document.querySelector('.alert');
    if(alert){ alert.style.display='none'; }
},3000);
</script>

</body>
</html>
