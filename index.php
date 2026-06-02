<?php
require_once 'includes/database.php';

// Get statistics for overview
$totalClients = $conn->query("SELECT COUNT(*) as count FROM Client")->fetch_assoc()['count'];
$totalEmployees = $conn->query("SELECT COUNT(*) as count FROM Employee")->fetch_assoc()['count'];
$totalItems = $conn->query("SELECT COUNT(*) as count FROM Rental_Item")->fetch_assoc()['count'];
$activeRentals = $conn->query("SELECT COUNT(*) as count FROM TransactionTbl WHERE return_date >= CURDATE()")->fetch_assoc()['count'];
$ongoingRentals = $conn->query("SELECT COUNT(*) as count FROM TransactionTbl WHERE return_date > CURDATE() AND start_date <= CURDATE()")->fetch_assoc()['count'];

// Get recent transactions
$recentTransactions = $conn->query("
    SELECT t.*, CONCAT(c.first_name, ' ', c.last_name) as client_name 
    FROM TransactionTbl t
    JOIN Client c ON t.client_id = c.client_id
    ORDER BY t.transaction_date DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table & Chair Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="includes/sidebar.css">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f0f2f5;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 25px 30px;
            min-height: 100vh;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 22px 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid;
            cursor: pointer;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .stat-card h3 {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0 5px 0;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card small {
            font-size: 12px;
            color: #999;
        }
        
        /* Custom Card */
        .card-custom {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        
        .card-custom .card-header {
            background: white;
            border-bottom: 2px solid #f0f2f5;
            padding: 18px 22px;
            font-weight: 600;
            font-size: 16px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .btn-custom:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46a0 100%);
            color: white;
        }
        
        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
            padding: 8px 22px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .badge-active {
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-ongoing {
            background: #f59e0b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            padding: 15px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .table tr {
            cursor: pointer;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-weight: 700; margin: 0;">Dashboard</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-custom" onclick="location.href='clients/add.php'">
                    <i class="fas fa-user-plus"></i> Add Client
                </button>
                <button class="btn btn-custom" onclick="location.href='transactions/create.php'">
                    <i class="fas fa-plus"></i> New Rental
                </button>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="mb-5">
            <h5 class="section-title">OVERVIEW</h5>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #667eea;" onclick="location.href='clients/list.php'">
                        <p><i class="fas fa-users"></i> CLIENTS</p>
                        <h3><?= $totalClients ?></h3>
                        <small>Registered Clients</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #10b981;" onclick="location.href='employees/list.php'">
                        <p><i class="fas fa-user-tie"></i> EMPLOYEES</p>
                        <h3><?= $totalEmployees ?></h3>
                        <small>Active Staff</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #f59e0b;" onclick="location.href='items/manage.php'">
                        <p><i class="fas fa-boxes"></i> INVENTORY</p>
                        <h3><?= $totalItems ?></h3>
                        <small>Total Items</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #ef4444;" onclick="location.href='transactions/view.php'">
                        <p><i class="fas fa-calendar-check"></i> ACTIVE RENTALS</p>
                        <h3><?= $activeRentals ?></h3>
                        <small>Ongoing: <?= $ongoingRentals ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card-custom">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i> Recent Transactions
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Client</th>
                            <th>Start Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recentTransactions && $recentTransactions->num_rows > 0): ?>
                            <?php while($row = $recentTransactions->fetch_assoc()): ?>
                            <tr onclick="location.href='transactions/view.php?id=<?= $row['transaction_id'] ?>'">
                                <td><?= $row['transaction_id'] ?></td>
                                <td><?= $row['client_name'] ?></td>
                                <td><?= $row['start_date'] ?></td>
                                <td><?= $row['return_date'] ?></td>
                                <td>
                                    <?php if($row['return_date'] < date('Y-m-d')): ?>
                                        <span class="badge-active">Completed</span>
                                    <?php else: ?>
                                        <span class="badge-ongoing">Ongoing</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No transactions yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>