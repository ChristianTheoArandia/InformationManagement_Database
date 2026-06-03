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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Rented Items - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/sidebar.css">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; margin: 0; }
        .main-content { margin-left: 280px; padding: 25px 30px; min-height: 100vh; }
        .report-card {
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
        .header-section h3 i { color: #f59e0b; margin-right: 10px; }
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
        .btn-back:hover { background: #4b5563; color: white; }
        .table { width: 100%; border-collapse: collapse; }
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
        .rank-badge {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #eef2ff;
            border-radius: 50%;
            text-align: center;
            line-height: 32px;
            font-weight: 700;
            color: #667eea;
        }
        .rank-1 {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }
        .rank-2 {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
            color: white;
        }
        .rank-3 {
            background: linear-gradient(135deg, #cd7f32, #b87333);
            color: white;
        }
        .empty-state { text-align: center; padding: 50px; color: #9ca3af; }
        .empty-state i { font-size: 60px; margin-bottom: 15px; }
        .table-responsive { overflow-x: auto; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="report-card">
            <div class="header-section">
                <h3><i class="fas fa-trophy"></i> Top 10 Most Rented Items</h3>
            </div>
            
            <?php if($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Times Rented</th>
                                <th>Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            while($row = $result->fetch_assoc()): 
                                $rankClass = '';
                                if($rank == 1) $rankClass = 'rank-1';
                                elseif($rank == 2) $rankClass = 'rank-2';
                                elseif($rank == 3) $rankClass = 'rank-3';
                            ?>
                            <tr>
                                <td><span class="rank-badge <?= $rankClass ?>"><?= $rank++ ?></span></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= $row['total_rented'] ?> times</span>
                                <td>₱<?= number_format($row['revenue'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <p>No rental data found</p>
                </div>
            <?php endif; ?>
            
            <div><a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Reports</a></div>
        </div>
    </div>
</body>
</html>