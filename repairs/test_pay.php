<?php
require_once '../includes/database.php';

$repair_id = 'R00001'; // Change to an actual repair ID

$repair = $conn->query("
    SELECT rf.* FROM Repair_Fee rf WHERE rf.repair_fee_id = '$repair_id'
")->fetch_assoc();

$total = ($repair['quantity'] ?? 1) * $repair['cost'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = 'P' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO Payment (payment_id, transaction_id, payment_date, amount, payment_type_id) 
            VALUES ('$payment_id', NULL, CURDATE(), '$total', '001')";
    
    if ($conn->query($sql)) {
        $conn->query("UPDATE Repair_Fee SET status = 'Paid' WHERE repair_fee_id = '$repair_id'");
        echo "<div style='color:green; padding:20px;'>✅ Repair payment successful! Redirecting...</div>";
        echo "<script>setTimeout(function(){ window.location.href = 'list.php'; }, 2000);</script>";
        exit();
    } else {
        echo "<div style='color:red; padding:20px;'>❌ Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Repair Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h4>Test Repair Payment for: <?= $repair_id ?></h4>
        </div>
        <div class="card-body">
            <p><strong>Amount Due:</strong> ₱<?= number_format($total, 2) ?></p>
            <form method="POST">
                <button type="submit" class="btn btn-success">Record Test Payment</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>