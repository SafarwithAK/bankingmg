<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$pdo = getPDO();

if(isset($_POST['change_pass'])){

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // 🔥 validation
    if($new !== $confirm){
        $_SESSION['pass_msg'] = "<div class='alert alert-danger'>New passwords do not match!</div>";
    }
    elseif(strlen($new) < 6){
        $_SESSION['pass_msg'] = "<div class='alert alert-danger'>Password must be at least 6 characters!</div>";
    }
    else{

        // 🔥 current password check
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id=?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if($admin && password_verify($current, $admin['password'])){

            $newHash = password_hash($new, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE admins SET password=? WHERE id=?");
            $stmt->execute([$newHash, $_SESSION['admin_id']]);

            $_SESSION['pass_msg'] = "<div class='alert alert-success'>Password changed successfully!</div>";

        } else {
            $_SESSION['pass_msg'] = "<div class='alert alert-danger'>Current password is incorrect!</div>";
        }
    }

    header("Location: admin_dashboard.php#");
    exit;
}
