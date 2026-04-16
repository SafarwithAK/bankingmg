<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
session_start();
requireLogin();

$pdo = getPDO();

$user_id = $_SESSION['user_id'];
$amount = (float)($_POST['amount'] ?? 0);

if($amount <= 0){
    $_SESSION['add_error'] = "Invalid amount.";
    header("Location: dashboard.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // 🔹 Get account id
    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    if(!$account){
        throw new Exception("Account not found");
    }

    // ➕ Update balance
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$amount, $account['id']]);

    // 🧾 Insert transaction (Credit)
    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (user_id, from_account_id, to_account_id, type, amount, description)
        VALUES (?, NULL, ?, 'Credit', ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $account['id'],
        $amount,
        'Self Deposit / Added Money'
    ]);

    $pdo->commit();

    $_SESSION['add_success'] = "₹".number_format($amount,2)." added successfully!";
    header("Location: dashboard.php");
    exit;

}catch(Exception $e){
    $pdo->rollBack();
    $_SESSION['add_error'] = "Failed: ".$e->getMessage();
    header("Location: dashboard.php");
    exit;
}
?>
