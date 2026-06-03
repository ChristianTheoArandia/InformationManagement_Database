<?php
require_once '../includes/database.php';

$transaction_id = $_GET['id'] ?? '';

if (!$transaction_id) {
    header("Location: list.php?error=No transaction selected");
    exit();
}

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

$items = $conn->query("
    SELECT ti.*, ri.item_name, ri.individual_cost
    FROM Transaction_Item ti
    JOIN Rental_Item ri ON ti.item_id = ri.item_id
    WHERE ti.transaction_id = '$transaction_id'
");

$isPaid = $transaction['payment_status'] == 'PAID';
$damageMessage = '';
$damageError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_damage'])) {
    $damaged_item_id = $_POST['item_id'];
    $damaged_quantity = (int)$_POST['damage_quantity'];
    $repair_cost = $_POST['repair_cost'];
    
    $repair_id = 'R' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO Repair_Fee (repair_fee_id, transaction_id, item_id, quantity, date_paid, status, cost) VALUES (?, ?, ?, ?, CURDATE(), 'Pending', ?)");
    $stmt->bind_param("sssid", $repair_id, $transaction_id, $damaged_item_id, $damaged_quantity, $repair_cost);
    
    if ($stmt->execute()) {
        $damageMessage = "Reported: $damaged_quantity unit(s) damaged. Total cost: ₱" . number_format($repair_cost, 2);
    } else {
        $damageError = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .info-row { display: flex; margin-bottom: 12px; }
        .info-label { width: 140px; font-weight: 600; color: #555; }
        .info-value { color: #333; }
        .btn-payment {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-damage {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
        }
        .table th { background: #f8f9fa; padding: 12px; }
        .table td { padding: 12px; vertical-align: middle; }
        .total-amount { font-size: 24px; font-weight: 700; color: #667eea; }
        .status-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        .status-paid { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .status-not-paid { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }
        .alert { border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .rental-days-badge {
            background: #eef2ff;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            color: #667eea;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>
<div class="page-card">
    <h3 class="mb-4"><i class="fas fa-receipt" style="color: #667eea;"></i> Transaction Details</h3>
    
    <?php if($damageMessage): ?>
        <div class="alert alert-success"><?= $damageMessage ?></div>
    <?php endif; ?>
    <?php if($damageError): ?>
        <div class="alert alert-danger"><?= $damageError ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="info-row"><div class="info-label">Transaction #:</div><div class="info-value"><?= $transaction['transaction_id'] ?></div></div>
            <div class="info-row"><div class="info-label">Client:</div><div class="info-value"><?= $transaction['client_name'] ?? 'N/A' ?></div></div>
            <div class="info-row"><div class="info-label">Employee:</div><div class="info-value"><?= $transaction['employee_name'] ?? 'N/A' ?></div></div>
        </div>
        <div class="col-md-6">
            <div class="info-row"><div class="info-label">Transaction Date:</div><div class="info-value"><?= $transaction['transaction_date'] ?></div></div>
            <div class="info-row"><div class="info-label">Start Date:</div><div class="info-value"><?= $transaction['start_date'] ?></div></div>
            <div class="info-row"><div class="info-label">Return Date:</div><div class="info-value"><?= $transaction['return_date'] ?></div></div>
            <div class="info-row">
                <div class="info-label">Rental Duration:</div>
                <div class="info-value">
                    <span class="rental-days-badge">
                        <i class="fas fa-calendar-week"></i> <?= $transaction['rental_duration'] ?> day(s)
                    </span>
                </div>
            </div>
            <div class="info-row"><div class="info-label">Payment Status:</div>
                <div class="info-value"><?php if($isPaid): ?><span class="status-badge status-paid">PAID</span><?php else: ?><span class="status-badge status-not-paid">NOT PAID</span><?php endif; ?></div>
            </div>
        </div>
    </div>
    
    <hr>
    
    <h5 class="section-title"><i class="fas fa-boxes"></i> Rented Items</h5>
    
    <?php if($transaction['rental_duration'] > 0): ?>
        <div class="alert alert-info" style="background: #e0f2fe; color: #0369a1; margin-bottom: 20px;">
            <i class="fas fa-info-circle"></i> 
            <strong>Note:</strong> Each item cost is multiplied by <?= $transaction['rental_duration'] ?> day(s) of rental period
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Daily Rate</th>
                    <th>Rental Days</th>
                    <th>Subtotal</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                $rental_duration = $transaction['rental_duration'] ?? 1;
                
                while($item = $items->fetch_assoc()): 
                    // Calculate subtotal: quantity × daily_rate × rental_duration
                    $subtotal = $item['quantity'] * $item['individual_cost'] * $rental_duration;
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₱<?= number_format($item['individual_cost'], 2) ?></td>
                    <td><?= $rental_duration ?> day(s)</td>
                    <td class="fw-bold text-primary">₱<?= number_format($subtotal, 2) ?></td>
                    <td>
                        <button type="button" class="btn-damage" onclick="openDamageModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['item_name']) ?>')">
                            <i class="fas fa-tools"></i> Report Damage
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr class="table-dark">
                    <td colspan="4"><strong>Total</strong></td>
                    <td colspan="2"><strong class="total-amount">₱<?= number_format($total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="mt-4 d-flex gap-3">
        <?php if($isPaid): ?>
            <button class="btn-payment" disabled style="opacity: 0.6; cursor: not-allowed;">Transaction Paid</button>
        <?php else: ?>
            <a href="../payments/process_payment.php?transaction_id=<?= $transaction_id ?>" class="btn-payment">
                <i class="fas fa-credit-card"></i> Process Payment (₱<?= number_format($total, 2) ?>)
            </a>
        <?php endif; ?>
        <a href="list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to List</a>
    </div>
</div>

<!-- Modal - Hidden by default -->
<div id="damageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 20px; padding: 30px; width: 450px; max-width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #f59e0b;"><i class="fas fa-tools"></i> Report Damaged Item</h3>
            <button onclick="closeDamageModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" id="damageForm">
            <input type="hidden" name="item_id" id="damageItemId">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 500; margin-bottom: 5px;">Item Name</label>
                <input type="text" id="damageItemName" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" readonly>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 500; margin-bottom: 5px;">Damaged Quantity</label>
                <input type="number" name="damage_quantity" id="damageQuantity" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" min="1" required>
                <small class="text-muted">How many units were damaged?</small>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 500; margin-bottom: 5px;">Total Repair Cost (₱)</label>
                <input type="number" name="repair_cost" id="repairCost" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" step="0.01" required>
                <small class="text-muted">Total cost for all damaged units</small>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeDamageModal()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">Cancel</button>
                <button type="submit" name="report_damage" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDamageModal(itemId, itemName) {
        document.getElementById('damageItemId').value = itemId;
        document.getElementById('damageItemName').value = itemName;
        document.getElementById('damageQuantity').value = '';
        document.getElementById('repairCost').value = '';
        document.getElementById('damageModal').style.display = 'flex';
    }
    
    function closeDamageModal() {
        document.getElementById('damageModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        let modal = document.getElementById('damageModal');
        if (event.target === modal) {
            closeDamageModal();
        }
    }
</script>
</body>
</html>