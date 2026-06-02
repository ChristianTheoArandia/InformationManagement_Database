<?php
require_once '../includes/database.php';

$year = $_GET['year'] ?? date('Y');

$sql = "SELECT 
            DATE_FORMAT(p.payment_date, '%M') as month,
            MONTH(p.payment_date) as month_num,
            COUNT(DISTINCT t.transaction_id) as transactions,
            SUM(p.amount) as revenue,
            COUNT(DISTINCT c.client_id) as clients
        FROM Payment p
        JOIN TransactionTbl t ON p.transaction_id = t.transaction_id
        JOIN Client c ON t.client_id = c.client_id
        WHERE YEAR(p.payment_date) = $year
        GROUP BY MONTH(p.payment_date)
        ORDER BY month_num";

$result = $conn->query($sql);
$months = [];
$revenues = [];
$transactions = [];
$clients = [];

while($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = $row['revenue'];
    $transactions[] = $row['transactions'];
    $clients[] = $row['clients'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Revenue Report - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .year-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .year-selector select {
            padding: 10px 16px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            font-family: 'Poppins', sans-serif;
            background: white;
        }
        .btn-view {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
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
            border: none;
        }
        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }
        .chart-container {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 16px;
        }
        canvas {
            max-height: 400px;
            width: 100%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }
        .total-revenue {
            font-size: 28px;
            font-weight: 700;
            color: #10b981;
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
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="header-section">
            <h3>
                <i class="fas fa-chart-line"></i> Monthly Revenue Report
            </h3>
            <form method="GET" class="year-selector">
                <select name="year">
                    <?php for($y = 2024; $y <= date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-view">
                    <i class="fas fa-search"></i> View
                </button>
            </form>
        </div>
        
        <?php if(empty($months)): ?>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No revenue data found for <?= $year ?></p>
            </div>
        <?php else: ?>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
            
            <h5 class="section-title">
                <i class="fas fa-table"></i> Monthly Breakdown
            </h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Transactions</th>
                            <th>Revenue</th>
                            <th>Unique Clients</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalRevenue = 0;
                        $totalTransactions = 0;
                        for($i = 0; $i < count($months); $i++): 
                            $totalRevenue += $revenues[$i];
                            $totalTransactions += $transactions[$i];
                        ?>
                        <tr>
                            <td><?= $months[$i] ?></td>
                            <td><?= $transactions[$i] ?></td>
                            <td>₱<?= number_format($revenues[$i], 2) ?></td>
                            <td><?= $clients[$i] ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8f9fa; font-weight: 600;">
                            <td><strong>Total</strong></td>
                            <td><strong><?= $totalTransactions ?></strong></td>
                            <td><strong class="total-revenue">₱<?= number_format($totalRevenue, 2) ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 25px;">
            <a href="../index.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <script>
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?= json_encode($revenues) ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>