<?php
require_once '../includes/database.php';

// Get transaction ID from URL
$transaction_id = $_GET['id'] ?? '';

if (!$transaction_id) {
    header("Location: list.php?error=No transaction selected");
    exit();
}

// Get transaction details
$sql = "SELECT t.*, 
        CONCAT(c.first_name, ' ', c.last_name) as client_name,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name
        FROM TransactionTbl t
        LEFT JOIN Client c ON t.client_id = c.client_id
        LEFT JOIN Employee e ON t.employee_id = e.employee_id
        WHERE t.transaction_id = '$transaction_id'";

$result = $conn->query($sql);
$transaction = $result->fetch_assoc();

if (!$transaction) {
    header("Location: list.php?error=Transaction not found");
    exit();
}

// Get items in this transaction
$items = $conn->query("
    SELECT ti.*, ri.item_name, ri.individual_cost
    FROM Transaction_Item ti
    JOIN Rental_Item ri ON ti.item_id = ri.item_id
    WHERE ti.transaction_id = '$transaction_id'
");

$isPaid = $transaction['payment_status'] == 'PAID';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; padding: 30px; }
        
        .page-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 900px;
            margin: 0 auto;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .info-label {
            width: 140px;
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .btn-payment {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            padding: 12px;
        }
        
        .table td {
            padding: 12px;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        
        .status-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .status-not-paid {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="page-card">
        <h3 class="mb-4">
            <i class="fas fa-receipt" style="color: #667eea;"></i> Transaction Details
        </h3>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-row">
                    <div class="info-label">Transaction #:</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['transaction_id']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Client:</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['client_name'] ?? 'N/A') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Employee:</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['employee_name'] ?? 'N/A') ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-row">
                    <div class="info-label">Transaction Date:</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['transaction_date']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Start Date:</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['start_date']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Return Date:</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['return_date']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Status:</div>
                    <div class="info-value">
                        <?php if($isPaid): ?>
                            <span class="status-badge status-paid">
                                <i class="fas fa-check-circle"></i> PAID
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-not-paid">
                                <i class="fas fa-times-circle"></i> NOT PAID
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        
        <h5 class="section-title">
            <i class="fas fa-boxes"></i> Rented Items
        </h5>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Daily Rate</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    if($items && $items->num_rows > 0):
                        while($item = $items->fetch_assoc()): 
                            $subtotal = $item['quantity'] * $item['individual_cost'];
                            $total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₱<?= number_format($item['individual_cost'], 2) ?></td>
                            <td>₱<?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No items in this transaction</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong class="total-amount">₱<?= number_format($total, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="mt-4 d-flex gap-3">
            <?php if($isPaid): ?>
                <!-- Disabled payment button for paid transactions -->
                <button class="btn-payment disabled" disabled onclick="return false;">
                    <i class="fas fa-check-circle"></i> Transaction Paid
                </button>
            <?php else: ?>
                <!-- Active payment button for unpaid transactions -->
                <a href="../payments/process_payment.php?transaction_id=<?= $transaction_id ?>" class="btn-payment">
                    <i class="fas fa-credit-card"></i> Process Payment 
                </a>
            <?php endif; ?>
            <a href="list.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</body>
</html>