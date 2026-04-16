<?php
require_once __DIR__ . '/../src/db.php';
session_start();

if(!isset($_SESSION['user_id'])){
    die("Unauthorized");
}

$pdo = getPDO();

$stmt = $pdo->prepare("
SELECT 
    u.full_name, u.email, u.username, u.created_at,
    u.mobile, u.address, u.dob, u.gender, u.nominee_name,
    a.account_number,
    b.branch_name, b.ifsc_code
FROM users u
JOIN accounts a ON u.id = a.user_id
LEFT JOIN branches b ON u.branch_id = b.id
WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$data = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Passbook - MyBank</title>

<style>
body{
    font-family: "Segoe UI", sans-serif;
    background:#eef3fb;
    margin:0;
}

/* PRINT */
@media print {
    body * { visibility:hidden; }
    .print-area, .print-area * { visibility:visible; }

    .print-area{
        position:absolute;
        left:0;
        top:0;
        width:100%;
    }

    .btn{
        display:none;
    }
}

/* PAGE */
.page{
    width:820px;
    margin:auto;
    background: linear-gradient(180deg,#ffffff,#f7fbff);
    border:3px solid #1e3a8a;
    padding:25px;
    position:relative;
    margin-bottom:20px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    border-radius:10px;
}

/* PREMIUM WATERMARK */
.page::before{
    content:"MY BANK";
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%) rotate(-30deg);
    font-size:110px;
    font-weight:900;
    color:rgba(30,58,138,0.06);
    letter-spacing:20px;
    z-index:0;
    white-space:nowrap;
}

/* CONTENT */
.content{
    position:relative;
    z-index:1;
}

/* HEADER */
.header{
    text-align:center;
    border-bottom:2px solid #1e3a8a;
    padding-bottom:10px;
}

.header h1{
    margin:0;
    color:#1e3a8a;
    font-size:28px;
    letter-spacing:2px;
}

.header p{
    margin:5px 0 0;
    font-size:12px;
    color:#555;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:13px;
    margin-top:10px;
}

th, td{
    border:1px solid #1e3a8a;
    padding:8px;
}

th{
    background:#1e3a8a;
    color:white;
}

/* BUTTON */
.btn{
    background:#1e3a8a;
    color:white;
    padding:10px 20px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

/* PHOTO BOX */
.photo{
    width:140px;
    height:160px;
    border:2px dashed #1e3a8a;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    color:#666;
    background:#f9fbff;
}
</style>
</head>

<body>

<div style="text-align:center; margin:20px;">
    <button class="btn" onclick="window.print()">🖨 Print Passbook</button>
</div>

<div class="print-area">

<!-- ================= PAGE ================= -->
<div class="page">

<div class="content">

<div class="header">
    <h1>🏦 MY BANK</h1>
    <p>Trusted Digital Banking System</p>
</div>

<!-- TOP SECTION -->
<div style="display:flex; justify-content:space-between; margin-top:20px; gap:15px;">

    <!-- LEFT -->
    <div style="width:35%;">
        <h3 style="color:#1e3a8a;">Bank Info</h3>
        <p style="font-size:13px; line-height:1.6;">
            Helpline: 1800-111-222<br>
            Email: support@mybank.com<br>
            Website: www.mybank.com
        </p>

        <h3 style="color:#1e3a8a;">Guidelines</h3>
        <ul style="font-size:12px;">
            <li>Never share OTP</li>
            <li>Bank never asks password</li>
            <li>Report fraud immediately</li>
        </ul>
    </div>

    <!-- PHOTO -->
    <div style="width:25%; text-align:center;">
        <div class="photo">PHOTO</div>
        <p style="font-size:12px;">Account Holder</p>
    </div>

    <!-- RIGHT -->
    <div style="width:40%; border:2px solid #1e3a8a; padding:15px; border-radius:10px; background:#f4f8ff;">
        <h3 style="color:#1e3a8a; margin-top:0;">Branch Details</h3>

        <p style="font-size:13px; line-height:1.8;">
            <b>Branch:</b> <?= $data['branch_name'] ?><br>
            <b>IFSC:</b> <?= $data['ifsc_code'] ?><br>
            <b>Account No:</b> <?= $data['account_number'] ?>
        </p>
    </div>

</div>

<!-- DETAILS TABLE -->
<h3 style="margin-top:25px; color:#1e3a8a;">Account Holder Details</h3>

<table>
<tr><th>Field</th><th>Details</th></tr>

<tr><td>Name</td><td><?= strtoupper($data['full_name']) ?></td></tr>
<tr><td>Account No</td><td><?= $data['account_number'] ?></td></tr>
<tr><td>Username</td><td><?= $data['username'] ?></td></tr>
<tr><td>Email</td><td><?= $data['email'] ?></td></tr>
<tr><td>Mobile</td><td><?= $data['mobile'] ?></td></tr>
<tr><td>DOB</td><td><?= $data['dob'] ?></td></tr>
<tr><td>Gender</td><td><?= $data['gender'] ?></td></tr>
<tr><td>Nominee</td><td><?= $data['nominee_name'] ?></td></tr>
<tr><td>Address</td><td><?= $data['address'] ?></td></tr>
<tr><td>Open Date</td><td><?= date('d M Y', strtotime($data['created_at'])) ?></td></tr>

</table>

<!-- SIGN + STAMP -->
<div style="display:flex; justify-content:space-between; margin-top:40px; align-items:flex-end;">

    <div style="display:flex; gap:80px;">
        <div style="text-align:center;">
            <div style="height:60px; width:180px; border-bottom:2px solid #1e3a8a;"></div>
            <p style="font-size:12px;">Account Holder</p>
        </div>

        <div style="text-align:center;">
            <div style="height:60px; width:180px; border-bottom:2px solid #1e3a8a;"></div>
            <p style="font-size:12px;">Bank Manager</p>
        </div>
    </div>

    <div style="
        width:120px;
        height:120px;
        border:3px dashed #1e3a8a;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        color:#1e3a8a;
        font-weight:bold;
        transform:rotate(-10deg);
    ">
        BANK<br>STAMP
    </div>

</div>

</div>
</div>

</div>

</body>
</html>
