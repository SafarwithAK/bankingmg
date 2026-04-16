<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$pdo = getPDO();

$id = $_GET['id'] ?? null;

// FETCH DATA
$stmt = $pdo->prepare("SELECT * FROM branches WHERE id=?");
$stmt->execute([$id]);
$branch = $stmt->fetch();

if(!$branch){
    die("Branch not found!");
}

// UPDATE
if(isset($_POST['update_branch'])){

    $name = trim($_POST['branch_name']);
    $ifsc = strtoupper(trim($_POST['ifsc_code']));
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $address = trim($_POST['address']);

    if(strlen($name) < 3){
        $_SESSION['branch_msg'] = "<div class='alert alert-danger'>Branch name too short!</div>";
    } elseif(strlen($ifsc) < 5){
        $_SESSION['branch_msg'] = "<div class='alert alert-danger'>Invalid IFSC!</div>";
    } else {

        $stmt = $pdo->prepare("
            UPDATE branches 
            SET branch_name=?, ifsc_code=?, city=?, state=?, address=? 
            WHERE id=?
        ");
        $stmt->execute([$name, $ifsc, $city, $state, $address, $id]);

        $_SESSION['branch_msg'] = "<div class='alert alert-success'>✅ Branch updated successfully!</div>";
    }

    header("Location: edit_branch.php?id=".$id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Branch | MyBank</title>
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
    max-width:600px;
    margin:30px auto;
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

/* TITLE */
.card-box h3{
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
        <i class="bi bi-bank2"></i> MyBank
    </span>

    <div>
        <a href="admin_dashboard.php#branches" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        <a href="logout.php" class="btn btn-danger btn-sm ms-2">
            Logout
        </a>
    </div>
</nav>

<!-- 🔥 FORM CARD -->
<div class="card-box">

    <h3>🏢 Edit Branch</h3>
    <hr>

    <!-- ALERT -->
    <?php
    if(isset($_SESSION['branch_msg'])){
        echo $_SESSION['branch_msg'];
        unset($_SESSION['branch_msg']);
    }
    ?>

    <form method="post" class="row g-3">

        <div class="col-md-12">
            <label>Branch Name</label>
            <input name="branch_name"
                   value="<?= htmlspecialchars($branch['branch_name']) ?>"
                   class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>IFSC Code</label>
            <input name="ifsc_code"
                   value="<?= htmlspecialchars($branch['ifsc_code']) ?>"
                   class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>City</label>
            <input name="city"
                   value="<?= htmlspecialchars($branch['city']) ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <label>State</label>
            <input name="state"
                   value="<?= htmlspecialchars($branch['state']) ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <label>Address</label>
            <input name="address"
                   value="<?= htmlspecialchars($branch['address']) ?>"
                   class="form-control">
        </div>

        <div class="col-md-6">
            <button name="update_branch" class="btn btn-primary w-100">
                Update Branch
            </button>
        </div>

        <div class="col-md-6">
            <a href="admin_dashboard.php#branches" class="btn btn-secondary w-100">
                Cancel
            </a>
        </div>

    </form>

</div>

<!-- AUTO HIDE ALERT -->
<script>
setTimeout(()=>{
    let alert = document.querySelector('.alert');
    if(alert){ alert.style.display = 'none'; }
},3000);
</script>

</body>
</html>
