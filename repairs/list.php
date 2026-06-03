<?php
require_once '../includes/database.php';

// Get all repair fees with client and item details including quantity
$repairs = $conn->query("
    SELECT rf.*, 
           CONCAT(c.first_name, ' ', c.last_name) as client_name, 
           ri.item_name,
           t.transaction_id
    FROM Repair_Fee rf
    JOIN TransactionTbl t ON rf.transaction_id = t.transaction_id
    JOIN Client c ON t.client_id = c.client_id
    JOIN Rental_Item ri ON rf.item_id = ri.item_id
    ORDER BY rf.date_paid DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repair Fees - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/sidebar.css">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f0f2f5;
            margin: 0;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 25px 30px;
            min-height: 100vh;
        }
        
        .repair-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header-section h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        
        .header-section h3 i {
            color: #f59e0b;
            margin-right: 10px;
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        
        .btn-back:hover {
            background: #4b5563;
            color: white;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table thead th {
            background: #f8f9fa;
            padding: 14px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: #555;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .table tbody td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .badge-pending {
            background: #f59e0b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .badge-paid {
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .quantity-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .alert {
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="repair-card">
            <div class="header-section">
                <h3>
                    <i class="fas fa-tools"></i> Repair Fee Management
                </h3>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <?php if($repairs && $repairs->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Repair ID</th>
                                <th>Transaction ID</th>
                                <th>Client</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Date Paid</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $repairs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['repair_fee_id']) ?></td>
                                <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td>
                                    <span class="quantity-badge">
                                        <i class="fas fa-box"></i> <?= $row['quantity'] ?? 1 ?> unit(s)
                                    </span>
                                </td>
                                <td><?= $row['date_paid'] ?></td>
                                <td>₱<?= number_format(($row['quantity'] ?? 1) * $row['cost'], 2) ?></td>
                                <td>
                                    <?php if($row['status'] == 'Paid'): ?>
                                        <span class="badge-paid"><i class="fas fa-check-circle"></i> Paid</span>
                                    <?php else: ?>
                                        <span class="badge-pending"><i class="fas fa-clock"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-tools"></i>
                    <p>No repair fees found</p>
                </div>
            <?php endif; ?>
            
            <div>
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>