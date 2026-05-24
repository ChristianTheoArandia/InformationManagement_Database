<?php
require_once '../includes/database.php';

$sql = "SELECT 
            ri.item_name,
            it.type_name as category,
            SUM(ti.quantity) as total_rented,
            ri.individual_cost,
            SUM(ti.quantity * ri.individual_cost) as revenue
        FROM Rental_Item ri
        JOIN Transaction_Item ti ON ri.item_id = ti.item_id
        JOIN Item_Type it ON ri.item_type_id = it.item_type_id
        GROUP BY ri.item_id
        ORDER BY total_rented DESC
        LIMIT 10";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Top Rented Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Top 10 Most Rented Items</h2>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr><th>Rank</th><th>Item</th><th>Category</th><th>Times Rented</th><th>Revenue Generated</th></tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $rank++ ?> <?= $rank == 2 ? '🥈' : ($rank == 3 ? '🥉' : '') ?></td>
                    <td><?= $row['item_name'] ?></td>
                    <td><?= $row['category'] ?></td>
                    <td><?= $row['total_rented'] ?> times</td>
                    <td>₱<?= number_format($row['revenue'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="../index.php" class="btn btn-secondary">Back</a>
    </div>
</body>
</html>