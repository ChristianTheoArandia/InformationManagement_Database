<?php
require_once '../includes/database.php';

$transaction_id = $_GET['id'] ?? '';

$sql = "SELECT t.*, 
        CONCAT(c.first_name, ' ', c.last_name) as client_name,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name
        FROM TransactionTbl t
        JOIN Client c ON t.client_id = c.client_id
        JOIN Employee e ON t.employee_id = e.employee_id
        WHERE t.transaction_id = '$transaction_id'";
$transaction = $conn->query($sql)->fetch_assoc();

$items = $conn->query("
    SELECT ti.*, ri.item_name, ri.individual_cost
    FROM Transaction_Item ti
    JOIN Rental_Item ri ON ti.item_id = ri.item_id
    WHERE ti.transaction_id = '$transaction_id'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4>Transaction #<?= $transaction_id ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Client:</strong> <?= $transaction['client_name'] ?></p>
                        <p><strong>Employee:</strong> <?= $transaction['employee_name'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Transaction Date:</strong> <?= $transaction['transaction_date'] ?></p>
                        <p><strong>Start Date:</strong> <?= $transaction['start_date'] ?></p>
                        <p><strong>Return Date:</strong> <?= $transaction['return_date'] ?></p>
                    </div>
                </div>
                
                <h5>Rented Items</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Item</th><th>Quantity</th><th>Daily Rate</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        while($item = $items->fetch_assoc()): 
                            $subtotal = $item['quantity'] * $item['individual_cost'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?= $item['item_name'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₱<?= $item['individual_cost'] ?></td>
                            <td>₱<?= $subtotal ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="table-dark">
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong>₱<?= $total ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <a href="process_payment.php?transaction_id=<?= $transaction_id ?>" class="btn btn-success">
                    Process Payment
                </a>
                <a href="../index.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</body>
</html>