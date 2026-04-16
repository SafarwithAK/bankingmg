<?php
session_start();

// 🔥 All session variables unset
$_SESSION = [];

// 🔥 Destroy session
session_destroy();

// 🔥 Prevent back button access (important)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 🔄 Redirect to login page
header("Location: csp_login.php");
exit;
?>
