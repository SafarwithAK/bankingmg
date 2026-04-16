<?php
session_start();
require_once __DIR__ . '/../src/db.php';


if(!isset($_SESSION['csp_id'])){ header("Location: csp_login.php"); exit; }

$pdo = getPDO();

$id = $_GET['id'] ?? null;

// FETCH USER
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if(!$user){
    die("User not found!");
}

// FETCH BRANCHES
$branches = $pdo->query("SELECT id, branch_name FROM branches")->fetchAll();

// UPDATE
if(isset($_POST['update_user'])){

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $nominee = trim($_POST['nominee_name']);
    $branch_id = $_POST['branch_id'];

    if(strlen($username) < 3){
        $_SESSION['user_msg'] = "<div class='alert alert-danger'>Username too short!</div>";
    }
    elseif(strlen($full_name) < 3){
        $_SESSION['user_msg'] = "<div class='alert alert-danger'>Full name too short!</div>";
    }
    else{

        $stmt = $pdo->prepare("
            UPDATE users SET 
                username=?, email=?, full_name=?, mobile=?, address=?, 
                dob=?, gender=?, nominee_name=?, branch_id=? 
            WHERE id=?
        ");

        $stmt->execute([
            $username, $email, $full_name, $mobile, $address,
            $dob, $gender, $nominee, $branch_id, $id
        ]);

        $_SESSION['user_msg'] = "<div class='alert alert-success'>✅ User updated successfully!</div>";
    }

    header("Location: edit_user.php?id=".$id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | MyBank</title>

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
    max-width:700px;
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
.form-control, .form-select{
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
        <a href="csp_dashboard.php#users" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        <a href="logout.php" class="btn btn-danger btn-sm ms-2">
            Logout
        </a>
    </div>
</nav>

<!-- 🔥 FORM CARD -->
<div class="card-box">

    <h3>👤 Edit User</h3>
    <hr>

    <!-- ALERT -->
    <?php
    if(isset($_SESSION['user_msg'])){
        echo $_SESSION['user_msg'];
        unset($_SESSION['user_msg']);
    }
    ?>

    <form method="post" class="row g-3">

        <!-- BASIC -->
        <div class="col-md-6">
            <label>Username</label>
            <input name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Email</label>
            <input name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
        </div>

        <div class="col-md-12">
            <label>Full Name</label>
            <input name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control" required>
        </div>

        <!-- CONTACT -->
        <div class="col-md-6">
            <label>Mobile</label>
            <input name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>" class="form-control">
        </div>

        <div class="col-md-6">
            <label>Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                <option <?= $user['gender']=='Male'?'selected':'' ?>>Male</option>
                <option <?= $user['gender']=='Female'?'selected':'' ?>>Female</option>
                <option <?= $user['gender']=='Other'?'selected':'' ?>>Other</option>
            </select>
        </div>

        <!-- EXTRA -->
        <div class="col-md-6">
            <label>DOB</label>
            <input type="date" name="dob" value="<?= $user['dob'] ?>" class="form-control">
        </div>

        <div class="col-md-6">
            <label>Nominee Name</label>
            <input name="nominee_name" value="<?= htmlspecialchars($user['nominee_name']) ?>" class="form-control">
        </div>

        <div class="col-md-12">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <!-- BRANCH -->
        <div class="col-md-12">
            <label>Branch</label>
            <select name="branch_id" class="form-select">
                <option value="">Select Branch</option>
                <?php foreach($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" 
                        <?= $user['branch_id']==$b['id']?'selected':'' ?>>
                        <?= htmlspecialchars($b['branch_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- BUTTONS -->
        <div class="col-md-6">
            <button name="update_user" class="btn btn-primary w-100">
                Update User
            </button>
        </div>

        <div class="col-md-6">
            <a href="csp_dashboard.php#users" class="btn btn-secondary w-100">
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
