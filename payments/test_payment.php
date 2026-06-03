<?php
require_once '../includes/database.php';

$transaction_id = 'T00001'; // Change to an actual transaction ID in your database

// Calculate total
$total = $conn->query("
    SELECT SUM(ti.quantity * ri.individual_cost) as total
    FROM Transaction_Item ti
    JOIN Rental_Item ri ON ti.item_id = ri.item_id
    WHERE ti.transaction_id = '$transaction_id'
")->fetch_assoc()['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = 'P' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $amount = $total;
    
    $sql = "INSERT INTO Payment (payment_id, transaction_id, payment_date, amount, payment_type_id) 
            VALUES ('$payment_id', '$transaction_id', CURDATE(), '$amount', '001')";
    
    if ($conn->query($sql)) {
        $conn->query("UPDATE TransactionTbl SET payment_status = 'PAID' WHERE transaction_id = '$transaction_id'");
        echo "<div style='color:green; padding:20px;'>✅ Payment successful! Redirecting...</div>";
        echo "<script>setTimeout(function(){ window.location.href = '../transactions/list.php'; }, 2000);</script>";
        exit();
    } else {
        echo "<div style='color:red; padding:20px;'>❌ Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4>Test Payment for Transaction: <?= $transaction_id ?></h4>
        </div>
        <div class="card-body">
            <p><strong>Amount Due:</strong> ₱<?= number_format($total, 2) ?></p>
            <form method="POST">
                <button type="submit" class="btn btn-success">Process Test Payment</button>
                <a href="../transactions/list.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>