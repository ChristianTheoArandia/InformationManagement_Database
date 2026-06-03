<?php
require_once '../includes/database.php';

// Get all transactions with client names
$transactions = $conn->query("
    SELECT t.*, CONCAT(c.first_name, ' ', c.last_name) as client_name
    FROM TransactionTbl t
    JOIN Client c ON t.client_id = c.client_id
    ORDER BY t.transaction_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Transactions - Table & Chair Rental</title>
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
        
        .transaction-card {
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
            color: #667eea;
            margin-right: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 500;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
            color: white;
        }
        
        .btn-view {
            background: #0ea5e9;
            color: white;
            padding: 6px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view:hover {
            background: #0284c7;
            color: white;
        }
        
        .btn-secondary {
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
        
        .btn-secondary:hover {
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
        
        .badge-active {
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .badge-completed {
            background: #6b7280;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
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

        .badge-paid {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

       .badge-not-paid {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="transaction-card">
            <div class="header-section">
                <h3>
                    <i class="fas fa-list"></i> All Transactions
                </h3>
                <a href="create.php" class="btn-primary">
                    <i class="fas fa-plus"></i> New Transaction
                </a>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <?php if($transactions && $transactions->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Client</th>
                                <th>Start Date</th>
                                <th>Return Date</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= $row['start_date'] ?></td>
                                <td><?= $row['return_date'] ?></td>
                                <td><?= htmlspecialchars($row['venue']) ?></td>
                                <td>
                                    <?php if($row['return_date'] < date('Y-m-d')): ?>
                                        <span class="badge-completed">Completed</span>
                                    <?php else: ?>
                                        <span class="badge-active">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $status = htmlspecialchars($row['payment_status']);
                                        if ($status == 'PAID'): ?>
                                            <span class="badge-paid">
                                            <i class="fas fa-check-circle"></i> <?= $status ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-not-paid">
                                            <i class="fas fa-times-circle"></i> <?= $status ?>
                                            </span>
                                        <?php endif; ?>

                                </td>
                                <td>
                                    <a href="view.php?id=<?= $row['transaction_id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions found</p>
                    <a href="create.php" class="btn-primary" style="display: inline-flex; margin-top: 15px;">
                        <i class="fas fa-plus"></i> Create Your First Transaction
                    </a>
                </div>
            <?php endif; ?>
            
            <div>
                <a href="../index.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>