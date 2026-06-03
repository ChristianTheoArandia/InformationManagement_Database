<?php
require_once '../includes/database.php';

// Get filter parameters
$payment_filter = $_GET['payment_status'] ?? '';
$rental_filter = $_GET['rental_status'] ?? '';

// Build the WHERE clause
$where_conditions = [];
$params = [];
$types = "";

if ($payment_filter && in_array($payment_filter, ['PAID', 'NOT PAID'])) {
    $where_conditions[] = "t.payment_status = ?";
    $params[] = $payment_filter;
    $types .= "s";
}

if ($rental_filter == 'ongoing') {
    $where_conditions[] = "t.return_date >= CURDATE()";
} elseif ($rental_filter == 'completed') {
    $where_conditions[] = "t.return_date < CURDATE()";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Get all transactions with filters
$sql = "
    SELECT t.*, CONCAT(c.first_name, ' ', c.last_name) as client_name
    FROM TransactionTbl t
    JOIN Client c ON t.client_id = c.client_id
    $where_clause
    ORDER BY t.transaction_date DESC
";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $transactions = $stmt->get_result();
} else {
    $transactions = $conn->query($sql);
}
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
        
        .filter-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
        }
        
        .filter-btn {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid #e5e7eb;
            background: white;
            color: #555;
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }
        
        .filter-btn:hover:not(.active) {
            background: #e5e7eb;
            transform: translateY(-1px);
        }
        
        .clear-btn {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            background: #6b7280;
            color: white;
            margin-left: 10px;
        }
        
        .clear-btn:hover {
            background: #4b5563;
            color: white;
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
            background: #f59e0b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-completed {
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        
        .result-count {
            font-size: 14px;
            color: #6b7280;
            margin-left: 15px;
        }
        
        @media (max-width: 768px) {
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                justify-content: space-between;
            }
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
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <span class="filter-label">Payment Status:</span>
                    <a href="?<?= http_build_query(array_merge($_GET, ['payment_status' => 'PAID', 'rental_status' => $rental_filter])) ?>" 
                       class="filter-btn <?= $payment_filter == 'PAID' ? 'active' : '' ?>">
                        <i class="fas fa-check-circle"></i> PAID
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['payment_status' => 'NOT PAID', 'rental_status' => $rental_filter])) ?>" 
                       class="filter-btn <?= $payment_filter == 'NOT PAID' ? 'active' : '' ?>">
                        <i class="fas fa-times-circle"></i> NOT PAID
                    </a>
                </div>
                
                <div class="filter-group">
                    <span class="filter-label">Rental Status:</span>
                    <a href="?<?= http_build_query(array_merge($_GET, ['rental_status' => 'ongoing', 'payment_status' => $payment_filter])) ?>" 
                       class="filter-btn <?= $rental_filter == 'ongoing' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-day"></i> Ongoing
                    </a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['rental_status' => 'completed', 'payment_status' => $payment_filter])) ?>" 
                       class="filter-btn <?= $rental_filter == 'completed' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check"></i> Completed
                    </a>
                </div>
                
                <?php if($payment_filter || $rental_filter): ?>
                    <a href="?" class="clear-btn">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
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
                                <th>Rental Status</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $transactions->fetch_assoc()): 
                                $isCompleted = $row['return_date'] < date('Y-m-d');
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($row['return_date'])) ?></td>
                                <td><?= htmlspecialchars($row['venue'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if($isCompleted): ?>
                                        <span class="badge-completed">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-active">
                                            <i class="fas fa-clock"></i> Ongoing
                                        </span>
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
                <div class="result-count">
                    <i class="fas fa-chart-bar"></i> Showing <?= $transactions->num_rows ?> transaction(s)
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions found</p>
                    <?php if($payment_filter || $rental_filter): ?>
                        <a href="?" class="btn-primary" style="display: inline-flex; margin-top: 15px;">
                            <i class="fas fa-undo"></i> Clear Filters
                        </a>
                    <?php else: ?>
                        <a href="create.php" class="btn-primary" style="display: inline-flex; margin-top: 15px;">
                            <i class="fas fa-plus"></i> Create Your First Transaction
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div>
                <a href="../index.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Optional: Add animation to filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Add loading state if needed
                console.log('Filter applied');
            });
        });
    </script>
</body>
</html>