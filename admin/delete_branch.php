<?php
require_once __DIR__ . '/../src/db.php';
session_start();

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$pdo = getPDO();

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    // Check branch exists
    $stmt = $pdo->prepare("SELECT id FROM branches WHERE id=?");
    $stmt->execute([$id]);

    if($stmt->fetch()){

        // Delete branch
        $stmt = $pdo->prepare("DELETE FROM branches WHERE id=?");
        $stmt->execute([$id]);

        $_SESSION['branch_msg'] = "<div class='alert alert-success'>Branch deleted successfully!</div>";

    } else {
        $_SESSION['branch_msg'] = "<div class='alert alert-danger'>Branch not found!</div>";
    }

} else {
    $_SESSION['branch_msg'] = "<div class='alert alert-danger'>Invalid request!</div>";
}

// Redirect back
header("Location: admin_dashboard.php#branches");
exit;
