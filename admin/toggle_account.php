<?php
require_once __DIR__ . '/../src/db.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit; }
$pdo = getPDO();

if(!isset($_GET['id'])) exit('Account ID missing');
$id = intval($_GET['id']);

// Fetch current status
$stmt = $pdo->prepare("SELECT status FROM accounts WHERE id=?");
$stmt->execute([$id]);
$account = $stmt->fetch();
if(!$account) exit('Account not found');

// Toggle status
$newStatus = $account['status']=='Active' ? 'Inactive' : 'Active';
$stmt = $pdo->prepare("UPDATE accounts SET status=? WHERE id=?");
$stmt->execute([$newStatus,$id]);

header("Location: admin_dashboard.php#accounts");
exit;
?>
