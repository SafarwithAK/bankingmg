<?php
session_start();

$amount = $_SESSION['amount'] ?? 0;
$method = $_POST['method'] ?? '';

if(!$amount || !$method){
    die("Invalid request");
}

$_SESSION['method'] = $method;
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">

<h3>Payment via <?= $method ?> | ₹<?= $amount ?></h3>

<form method="POST" action="process_payment.php">

<input type="hidden" name="amount" value="<?= $amount ?>">
<input type="hidden" name="method" value="<?= $method ?>">

<?php if($method == "UPI"): ?>

    <label>UPI ID</label>
    <input type="text" name="upi_id" class="form-control" required placeholder="example@upi">

<?php elseif($method == "CARD"): ?>

    <label>Card Number</label>
    <input type="text" name="card_number" class="form-control" required>

    <label>Expiry</label>
    <input type="text" name="expiry" class="form-control" required placeholder="MM/YY">

    <label>CVV</label>
    <input type="password" name="cvv" class="form-control" required>

<?php elseif($method == "NETBANKING"): ?>

    <label>Bank Name</label>
    <input type="text" name="bank" class="form-control" required>

    <label>User ID</label>
    <input type="text" name="user_id" class="form-control" required>

    <label>Password</label>
    <input type="password" name="password" class="form-control" required>

<?php endif; ?>

<button class="btn btn-success mt-3">Pay Now</button>

</form>

</div>

</body>
</html>
