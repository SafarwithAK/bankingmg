<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if(!isset($_SESSION['csp_id'])){
    header("Location: csp_login.php");
    exit;
}

$pdo = getPDO();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $csp_id = $_SESSION['csp_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if(empty($current_password) || empty($new_password) || empty($confirm_password)){
        $_SESSION['error_msg'] = "All fields are required!";
        header("Location: csp_dashboard.php?section=change_password");
        exit;
    }

    if($new_password !== $confirm_password){
        $_SESSION['error_msg'] = "New password & confirm password not match!";
        header("Location: csp_dashboard.php?section=change_password");
        exit;
    }

    // Get old password
    $stmt = $pdo->prepare("SELECT password FROM csp_users WHERE id=?");
    $stmt->execute([$csp_id]);
    $user = $stmt->fetch();

    if(!$user){
        $_SESSION['error_msg'] = "User not found!";
        header("Location: csp_dashboard.php?section=change_password");
        exit;
    }

    // Verify current password
    if(!password_verify($current_password, $user['password'])){
        $_SESSION['error_msg'] = "Current password is wrong!";
        header("Location: csp_dashboard.php?section=change_password");
        exit;
    }

    // Update password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE csp_users SET password=? WHERE id=?");
    $stmt->execute([$new_hash, $csp_id]);

    $_SESSION['success_msg'] = "Password changed successfully!";
    header("Location: csp_dashboard.php?section=change_password");
    exit;
}
?>
