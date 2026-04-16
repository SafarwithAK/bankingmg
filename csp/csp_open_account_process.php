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

// 🔹 Get CSP branch
$stmt = $pdo->prepare("SELECT branch_id FROM csp_users WHERE id=?");
$stmt->execute([$csp_id]);
$csp = $stmt->fetch();
$branch_id = $csp['branch_id'] ?? 0;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $nominee_name = trim($_POST['nominee_name']);
    $password = $_POST['password'];

    $errors = [];

    if(!$username) $errors[] = "Username is required.";
    if(!$full_name) $errors[] = "Full Name is required.";
    if(!$email) $errors[] = "Email is required.";
    if(!$password) $errors[] = "Password is required.";

    // Duplicate check
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $stmt->execute([$username, $email]);
    if($stmt->rowCount() > 0) $errors[] = "Username or Email already exists.";

    if(empty($errors)){
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, full_name, email, mobile, password_hash, branch_id, address, dob, gender, nominee_name, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$username, $full_name, $email, $mobile, $password_hash, $branch_id, $address, $dob, $gender, $nominee_name]);

        $user_id = $pdo->lastInsertId();

        // Generate account number and create account
        $account_number = generateAccountNumber();
        $stmt = $pdo->prepare("INSERT INTO accounts (user_id, account_number, balance)
            VALUES (?, ?, 1000)");
        $stmt->execute([$user_id, $account_number]);

        $_SESSION['success_msg'] = "Customer account created successfully! Account No: $account_number";
    }else{
        $_SESSION['error_msg'] = implode('<br>', $errors);
    }

    header("Location: csp_dashboard.php#open_account");
    exit;
}
