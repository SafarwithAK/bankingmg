<?php
require_once __DIR__ . '/../src/db.php';
$pdo = getPDO();
$message = '';

if(isset($_POST['register'])){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?,?,?)");
    if($stmt->execute([$username, $email, $password])){
        $message = "<div class='alert alert-success'>Admin registered successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: Could not register admin.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Register - MyBank</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#0044cc,#007bff);
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Segoe UI';
}

/* CARD */
.register-card{
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    width:100%;
    max-width:420px;
}

/* LOGO */
.logo{
    text-align:center;
    font-size:26px;
    font-weight:bold;
    color:#0044cc;
    margin-bottom:20px;
}

/* BUTTON */
.btn-gradient{
    background:linear-gradient(90deg,#0044cc,#007bff);
    color:white;
    border:none;
    font-weight:600;
}
.btn-gradient:hover{
    opacity:0.9;
}

/* INPUT */
.form-control{
    border-radius:8px;
}

/* LINK */
a{
    text-decoration:none;
}
</style>

</head>
<body>

<div class="register-card">

    <div class="logo">
        <i class="bi bi-person-plus"></i> Admin Register
    </div>

    <h4 class="text-center mb-3">Create Account</h4>

    <?= $message ?>

    <form method="post">
        <div class="mb-3">
            <label class="fw-semibold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
        </div>

        <div class="mb-3">
            <label class="fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
        </div>

        <div class="mb-3">
            <label class="fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button class="btn btn-gradient w-100" name="register">
            <i class="bi bi-person-check"></i> Register
        </button>

        <p class="mt-3 text-center">
            Already registered? <a href="admin_login.php">Login</a>
        </p>
    </form>

</div>

</body>
</html>
