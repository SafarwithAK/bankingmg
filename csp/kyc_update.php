<?php
require_once __DIR__ . '/../src/db.php';

if(isset($_GET['id']) && isset($_GET['status'])){
    $pdo = getPDO();

    $id = $_GET['id'];
    $status = $_GET['status'];

    // सुरक्षा check
    if(!in_array($status, ['Pending','Verified','Rejected'])){
        die("Invalid Status");
    }

    $stmt = $pdo->prepare("UPDATE users SET kyc_status=? WHERE id=?");
    $stmt->execute([$status, $id]);

    header("Location: csp_dashboard.php");
    exit;
}
?>
