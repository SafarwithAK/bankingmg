<?php
session_start();
require_once __DIR__ . '/../src/db.php';

$pdo = getPDO();

$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];
$method = $_POST['method'];

if($amount <= 0){
    die("Invalid amount");
}

try {
    $pdo->beginTransaction();

    // balance update
    $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE user_id=?")
        ->execute([$amount, $user_id]);

    // transaction insert
    $pdo->prepare("
        INSERT INTO transactions
        (user_id, to_account_id, type, amount, description, created_at)
        VALUES (?, (SELECT id FROM accounts WHERE user_id=?), 'Credit', ?, ?, NOW())
    ")->execute([
        $user_id,
        $user_id,
        $amount,
        "Added via $method"
    ]);

    $pdo->commit();

    echo "<h2>Payment Successful via $method</h2>";
    echo "<a href='dashboard.php'>Back</a>";

} catch(Exception $e){
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
