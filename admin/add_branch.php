<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$pdo = getPDO();

if(isset($_POST['add_branch'])){

    $branch_name = trim($_POST['branch_name']);
    $ifsc = strtoupper(trim($_POST['ifsc_code']));
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);

    if(strlen($branch_name) < 3){
        $_SESSION['branch_msg'] = "<div class='alert alert-danger'>Branch name too short!</div>";
    }
    elseif(strlen($ifsc) < 5){
        $_SESSION['branch_msg'] = "<div class='alert alert-danger'>Invalid IFSC code!</div>";
    }
    else{

        $stmt = $pdo->prepare("SELECT id FROM branches WHERE ifsc_code=?");
        $stmt->execute([$ifsc]);

        if($stmt->fetch()){
            $_SESSION['branch_msg'] = "<div class='alert alert-danger'>IFSC already exists!</div>";
        } else {

            $stmt = $pdo->prepare("
                INSERT INTO branches (branch_name, ifsc_code, address, city, state)
                VALUES (?,?,?,?,?)
            ");
            $stmt->execute([$branch_name, $ifsc, $address, $city, $state]);

            $_SESSION['branch_msg'] = "<div class='alert alert-success'>Branch added successfully!</div>";
        }
    }

    header("Location: admin_dashboard.php#branches");
    exit;
}
