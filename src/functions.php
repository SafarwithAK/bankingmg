<?php
// src/functions.php
session_start();

function generateAccountNumber(){
    // simple random account number: 12 digits
    return str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
}

function isLoggedIn(){
    return isset($_SESSION['user_id']);
}

function requireLogin(){
    if(!isLoggedIn()){
        header('Location: login.php'); exit;
    }
}
