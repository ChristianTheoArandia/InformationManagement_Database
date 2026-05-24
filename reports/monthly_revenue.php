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
while($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = $row['revenue'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Revenue Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Monthly Revenue Report - <?= $year ?></h2>
        
        <canvas id="revenueChart" height="100"></canvas>
        
        <table class="table table-bordered mt-4">
            <thead>
                <tr><th>Month</th><th>Transactions</th><th>Revenue</th><th>Unique Clients</th></tr>
            </thead>
            <tbody>
                <?php 
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $row['month'] ?></td>
                    <td><?= $row['transactions'] ?></td>
                    <td>₱<?= number_format($row['revenue'], 2) ?></td>
                    <td><?= $row['clients'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?= json_encode($revenues) ?>,
                    backgroundColor: 'blue'
                }]
            }
        });
    </script>
</body>
</html>