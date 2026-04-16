<?php
session_start();
require_once __DIR__ . '/../src/db.php';

header("Content-Type: application/json");

if(!isset($_SESSION['csp_id'])){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$pdo = getPDO();
$data = json_decode(file_get_contents("php://input"), true);

$account_number = $data['account_number'] ?? '';
$type = $data['type'] ?? '';
$amount = floatval($data['amount'] ?? 0);
$csp_id = $_SESSION['csp_id'];

/* ================= GET CSP ================= */
$stmt = $pdo->prepare("SELECT * FROM csp_users WHERE id=?");
$stmt->execute([$csp_id]);
$csp = $stmt->fetch();

if(!$csp){
    echo json_encode(["status"=>"error","message"=>"CSP not found"]);
    exit;
}

/* ================= GET ACCOUNT ================= */
$stmt = $pdo->prepare("
    SELECT a.*, u.id as user_id, u.full_name
    FROM accounts a
    JOIN users u ON u.id = a.user_id
    WHERE a.account_number = ?
");
$stmt->execute([$account_number]);
$account = $stmt->fetch();

if(!$account && $type !== "statement"){
    echo json_encode(["status"=>"error","message"=>"Invalid account"]);
    exit;
}

try {
    $pdo->beginTransaction();

    /* ================= DEPOSIT ================= */
    if($type === "deposit"){

        if($amount <= 0){
            throw new Exception("Invalid amount");
        }

        if($csp['balance'] < $amount){
            throw new Exception("CSP balance insufficient");
        }

        // ➖ CSP balance
        $pdo->prepare("UPDATE csp_users SET balance = balance - ? WHERE id=?")
            ->execute([$amount, $csp_id]);

        // ➕ User balance
        $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id=?")
            ->execute([$amount, $account['id']]);

        // 🧾 Transaction (Credit)
        $pdo->prepare("
            INSERT INTO transactions 
            (user_id, from_account_id, to_account_id, type, amount, description)
            VALUES (?, NULL, ?, 'Credit', ?, ?)
        ")->execute([
            $account['user_id'],
            $account['id'],
            $amount,
            "Deposit by CSP"
        ]);

        echo json_encode([
            "status"=>"success",
            "message"=>"Deposit successful"
        ]);
    }

    /* ================= WITHDRAW ================= */
    elseif($type === "withdraw"){

        if($amount <= 0){
            throw new Exception("Invalid amount");
        }

        if($account['balance'] < $amount){
            throw new Exception("Insufficient balance");
        }

        // ➖ User balance
        $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id=?")
            ->execute([$amount, $account['id']]);

        // ➕ CSP balance
        $pdo->prepare("UPDATE csp_users SET balance = balance + ? WHERE id=?")
            ->execute([$amount, $csp_id]);

        // 🧾 Transaction (Debit)
        $pdo->prepare("
            INSERT INTO transactions 
            (user_id, from_account_id, to_account_id, type, amount, description)
            VALUES (?, ?, NULL, 'Debit', ?, ?)
        ")->execute([
            $account['user_id'],
            $account['id'],
            $amount,
            "Withdraw by CSP"
        ]);

        echo json_encode([
            "status"=>"success",
            "message"=>"Withdraw successful"
        ]);
    }

    /* ================= BALANCE ================= */
    elseif($type === "balance"){

        echo json_encode([
            "status"=>"success",
            "balance"=>$account['balance']
        ]);
    }

    /* ================= MINI STATEMENT ================= */
   elseif($type === "statement"){

    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.type,
            t.amount,
            t.description,
            t.created_at,

            u.full_name,
            fa.account_number AS from_acc,
            ta.account_number AS to_acc

        FROM transactions t

        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users u ON u.id = t.user_id

        WHERE t.user_id = ?

        ORDER BY t.id DESC
        LIMIT 10
    ");

    $stmt->execute([$account['user_id']]);
    $rows = $stmt->fetchAll();

    $result = [];

    foreach($rows as $t){

        $result[] = [
            "type" => $t['type'],
            "amount" => $t['amount'],
            "description" => $t['description'],
            "date" => $t['created_at'],
            "user" => $t['full_name'],

            "from_account" => $t['from_acc'],
            "to_account" => $t['to_acc']
        ];
    }

    echo json_encode([
        "status" => "success",
        "data" => $result
    ]);
}
    else {
        throw new Exception("Invalid transaction type");
    }

    $pdo->commit();

} catch(Exception $e){
    $pdo->rollBack();
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}