<?php
// public/logout.php
require_once __DIR__ . '/../src/functions.php';
session_start();
session_unset();
session_destroy();
header('Location: login.php'); exit;
