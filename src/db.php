<?php
// src/db.php
function getPDO(){
    // change these to your DB credentials
    $host = '127.0.0.1';
    $db   = 'bank_app';
    $user = 'root';
    $pass = 'Ajit@9334';
    $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}
