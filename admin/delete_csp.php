<?php
session_start();

require_once __DIR__ . '/../src/db.php';

// 🔥 IMPORTANT
$pdo = getPDO();

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

// DELETE CSP
if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    $stmt = $pdo->prepare("DELETE FROM csp_users WHERE id = ?");
    $stmt->execute([$id]);
}

// REDIRECT BACK
header("Location: admin_dashboard.php#csp");
exit;
