<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$pdo = getPDO();

if(isset($_POST['add_csp'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $branch_id = $_POST['branch_id'];
    $password = $_POST['password'];

    if(strlen($name) < 3){
        $_SESSION['csp_msg'] = "<div class='alert alert-danger'>Name too short!</div>";
    }
    else{

        $passHash = password_hash($password, PASSWORD_DEFAULT);

       $stmt = $pdo->prepare("
    INSERT INTO csp_users (branch_id, name, email, mobile, address, password)
    VALUES (?,?,?,?,?,?)
");
$stmt->execute([$branch_id, $name, $email, $mobile, $address, $passHash]);

   $_SESSION['csp_msg'] = "<div class='alert alert-success'>CSP added successfully!</div>";

    }

    header("Location: admin_dashboard.php#csp");
    exit;
}
