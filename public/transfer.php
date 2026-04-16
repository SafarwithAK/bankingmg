<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
requireLogin();

$pdo = getPDO();

$from_user_id = $_SESSION['user_id'];
$to_account = $_POST['to_account'];
$amount = floatval($_POST['amount']);
$description = $_POST['description'] ?? '';

if($amount <= 0){
    $_SESSION['transfer_error'] = "Invalid amount!";
    header("Location: dashboard.php#transfer");
    exit;
}

try {

    $pdo->beginTransaction();

    // ======================
    // 1. GET SENDER ACCOUNT
    // ======================
    $stmt = $pdo->prepare("SELECT id, balance FROM accounts WHERE user_id=?");
    $stmt->execute([$from_user_id]);
    $fromAcc = $stmt->fetch();

    if(!$fromAcc){
        throw new Exception("Sender account not found");
    }

    if($fromAcc['balance'] < $amount){
        throw new Exception("Insufficient balance");
    }

    // ======================
    // 2. GET RECEIVER ACCOUNT
    // ======================
    $stmt = $pdo->prepare("SELECT id, user_id FROM accounts WHERE account_number=?");
    $stmt->execute([$to_account]);
    $toAcc = $stmt->fetch();

    if(!$toAcc){
        throw new Exception("Receiver account not found");
    }

    if($fromAcc['id'] == $toAcc['id']){
        throw new Exception("You cannot send money to yourself");
    }

    // ======================
    // 3. UPDATE BALANCES
    // ======================

    // debit sender
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id=?");
    $stmt->execute([$amount, $fromAcc['id']]);

    // credit receiver
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id=?");
    $stmt->execute([$amount, $toAcc['id']]);

    // ======================
    // 4. INSERT TRANSACTION
    // ======================

    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (from_account_id, to_account_id, type, amount, description)
        VALUES (?, ?, 'transfer', ?, ?)
    ");

    $stmt->execute([
        $fromAcc['id'],
        $toAcc['id'],
        $amount,
        $description
    ]);

    $pdo->commit();

    $_SESSION['transfer_success'] = "₹$amount transferred successfully!";

} catch(Exception $e){
    $pdo->rollBack();
    $_SESSION['transfer_error'] = $e->getMessage();
}

header("Location: dashboard.php#transfer");
exit;
