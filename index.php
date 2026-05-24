<?php
require_once 'includes/database.php';

// Get counts for dashboard
$clientCount = $conn->query("SELECT COUNT(*) as count FROM Client")->fetch_assoc()['count'];
$employeeCount = $conn->query("SELECT COUNT(*) as count FROM Employee")->fetch_assoc()['count'];
$itemCount = $conn->query("SELECT COUNT(*) as count FROM Rental_Item")->fetch_assoc()['count'];
$revenueResult = $conn->query("SELECT SUM(amount) as total FROM Payment")->fetch_assoc();
$totalRevenue = $revenueResult['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store"></i> Rental System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i> Clients
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="clients/add.php">Add Client</a></li>
                            <li><a class="dropdown-item" href="clients/list.php">View Clients</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-tie"></i> Employees
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="employees/add.php">Add Employee</a></li>
                            <li><a class="dropdown-item" href="employees/list.php">View Employees</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-box"></i> Items
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="items/add_item.php">Add Item</a></li>
                            <li><a class="dropdown-item" href="items/manage.php">Manage Items</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-line"></i> Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="reports/monthly_revenue.php">Monthly Revenue</a></li>
                            <li><a class="dropdown-item" href="reports/top_items.php">Top Rented Items</a></li>
                            <li><a class="dropdown-item" href="reports/client_history.php">Client History</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions/create.php">
                            <i class="fas fa-shopping-cart"></i> New Transaction
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Dashboard</h1>
        
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card text-white bg-primary" onclick="location.href='clients/list.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Clients</h5>
                                <h2><?= $clientCount ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card text-white bg-success" onclick="location.href='employees/list.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Employees</h5>
                                <h2><?= $employeeCount ?></h2>
                            </div>
                            <i class="fas fa-user-tie fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card text-white bg-info" onclick="location.href='items/manage.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Items</h5>
                                <h2><?= $itemCount ?></h2>
                            </div>
                            <i class="fas fa-box fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card text-white bg-warning" onclick="location.href='reports/monthly_revenue.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Revenue</h5>
                                <h2>₱<?= number_format($totalRevenue, 2) ?></h2>
                            </div>
                            <i class="fas fa-money-bill-wave fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-clock"></i> Recent Transactions
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Transaction ID</th><th>Client</th><th>Date</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent = $conn->query("
                                    SELECT t.transaction_id, CONCAT(c.first_name, ' ', c.last_name) as client_name, 
                                           t.transaction_date, 
                                           CASE WHEN t.return_date < CURDATE() THEN 'Completed' ELSE 'Active' END as status
                                    FROM TransactionTbl t
                                    JOIN Client c ON t.client_id = c.client_id
                                    ORDER BY t.transaction_date DESC LIMIT 5
                                ");
                                while($row = $recent->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $row['transaction_id'] ?></td>
                                    <td><?= $row['client_name'] ?></td>
                                    <td><?= $row['transaction_date'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-calendar"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="transactions/create.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus"></i> New Rental Transaction
                            </a>
                            <a href="clients/add.php" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus"></i> Register New Client
                            </a>
                            <a href="reports/client_history.php" class="btn btn-info btn-lg">
                                <i class="fas fa-history"></i> View Client History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>