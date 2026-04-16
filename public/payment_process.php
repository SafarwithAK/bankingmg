<?php
session_start();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
requireLogin();

$pdo = getPDO();

$user_id = $_SESSION['user_id'];
$amount = (float)($_POST['amount'] ?? 0);
$method = $_POST['method'] ?? '';

if($amount <= 0){
    die("Invalid amount");
}

try {

    $pdo->beginTransaction();

    // account
    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE user_id=?");
    $stmt->execute([$user_id]);
    $acc = $stmt->fetch();

    // 1️⃣ BALANCE UPDATE
    $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE user_id=?")
        ->execute([$amount, $user_id]);

    // 2️⃣ TRANSACTION INSERT (IMPORTANT - YOUR TABLE FIXED)
    $desc = "Added via " . strtoupper($method);

    if($method == "upi"){
        $desc .= " (UPI)";
    }
    if($method == "card"){
        $desc .= " (CARD)";
    }

    $pdo->prepare("
        INSERT INTO transactions
        (user_id, from_account_id, to_account_id, type, amount, description, created_at)
        VALUES (?, NULL, ?, 'Credit', ?, ?, NOW())
    ")->execute([
        $user_id,
        $acc['id'],
        $amount,
        $desc
    ]);

    $pdo->commit();

    header("Location: dashboard.php#addMoney");
    exit;

} catch(Exception $e){
    $pdo->rollBack();
    echo "Payment Failed: " . $e->getMessage();
}
