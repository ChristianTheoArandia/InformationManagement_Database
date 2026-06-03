<?php
require_once '../includes/database.php';

// Default to showing 7 days (1 week)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$days_to_show = isset($_GET['days']) ? (int)$_GET['days'] : 7;

// Calculate end date
$end_date = date('Y-m-d', strtotime($start_date . ' + ' . ($days_to_show - 1) . ' days'));

// Get damaged items count (SUM of quantities, not COUNT of reports)
$damaged_query = $conn->query("
    SELECT item_id, SUM(quantity) as damaged_count 
    FROM Repair_Fee 
    WHERE status != 'Completed' AND status != 'Paid'
    GROUP BY item_id
");
$damaged_items = [];
while ($d = $damaged_query->fetch_assoc()) {
    $damaged_items[$d['item_id']] = $d['damaged_count'];
}

// Get all items
$all_items = $conn->query("SELECT * FROM Rental_Item ORDER BY item_type_id, item_id");

// Store items in array
$items = [];
while ($item = $all_items->fetch_assoc()) {
    $items[] = $item;
}

// Generate date range
$dates = [];
$current = strtotime($start_date);
$end = strtotime($end_date);
while ($current <= $end) {
    $dates[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

// For each item, calculate availability for each date
$availability_matrix = [];
foreach ($items as $item) {
    $item_id = $item['item_id'];
    $availability_matrix[$item_id] = [
        'item' => $item,
        'availability' => []
    ];
    
    foreach ($dates as $date) {
        // Calculate rented quantity for this specific date
        $rented_query = $conn->prepare("
            SELECT COALESCE(SUM(ti.quantity), 0) as rented_count
            FROM Transaction_Item ti
            JOIN TransactionTbl t ON ti.transaction_id = t.transaction_id
            WHERE ti.item_id = ? 
            AND ? BETWEEN t.start_date AND t.return_date
        ");
        $rented_query->bind_param("ss", $item_id, $date);
        $rented_query->execute();
        $rented_result = $rented_query->get_result();
        $rented_data = $rented_result->fetch_assoc();
        $rented_count = $rented_data['rented_count'];
        
        // Subtract rented AND damaged items from total stock
        $damaged_count = $damaged_items[$item_id] ?? 0;
        $available_stock = $item['total_stock'] - $rented_count - $damaged_count;
        
        $availability_matrix[$item_id]['availability'][$date] = [
            'rented' => $rented_count,
            'damaged' => $damaged_count,
            'available' => max(0, $available_stock),
            'status' => $available_stock <= 0 ? 'out' : ($available_stock < 5 ? 'low' : 'available')
        ];
    }
}

// Separate items by type
$chairs = array_filter($items, function($item) {
    return $item['item_type_id'] == '001';
});
$tables = array_filter($items, function($item) {
    return $item['item_type_id'] == '002';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Availability Calendar - Table & Chair Rental</title>
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
        
        .availability-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
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
        
        .control-panel {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .control-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 180px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 13px;
            color: #555;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #374151;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }
        
        .availability-calendar {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 800px;
            margin-bottom: 20px;
        }
        
        .availability-calendar th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .availability-calendar td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .item-name-cell {
            font-weight: 600;
            text-align: left;
            background: white;
            width: 180px;
        }
        
        .availability-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            min-width: 65px;
        }
        
        .status-available {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-low {
            background: #fed7aa;
            color: #ea580c;
        }
        
        .status-out {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .rented-count {
            font-size: 10px;
            color: #6b7280;
            margin-top: 3px;
        }
        
        .damaged-count {
            font-size: 10px;
            color: #dc2626;
            margin-top: 3px;
        }
        
        .stock-info {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 5px;
        }
        
        .legend {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 12px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 6px;
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
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #4b5563;
            color: white;
            transform: translateX(-3px);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="availability-card">
            <div class="header-section">
                <h3>
                    <i class="fas fa-calendar-alt"></i> Daily Availability Calendar
                </h3>
            </div>
            
            <div class="control-panel">
                <form method="GET" action="" class="control-form">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= $start_date ?>">
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <select name="days">
                            <option value="1" <?= $days_to_show == 1 ? 'selected' : '' ?>>1 Day</option>
                            <option value="3" <?= $days_to_show == 3 ? 'selected' : '' ?>>3 Days</option>
                            <option value="7" <?= $days_to_show == 7 ? 'selected' : '' ?>>1 Week (7 Days)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-sync-alt"></i> Refresh Calendar
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #d1fae5;"></div>
                    <span>Available (≥5 units)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #fed7aa;"></div>
                    <span>Low Stock (<5 units)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #fee2e2;"></div>
                    <span>Out of Stock (0 units)</span>
                </div>
                <div class="legend-item">
                    <i class="fas fa-tools" style="color: #dc2626;"></i>
                    <span>Units in Repair</span>
                </div>
            </div>
            
            <!-- CHAIRS SECTION -->
            <div>
                <h4 class="section-title">
                    <i class="fas fa-chair"></i> Chairs Availability
                </h4>
                <div class="table-responsive">
                    <table class="availability-calendar">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <?php foreach($dates as $date): ?>
                                    <th><?= date('D, M j', strtotime($date)) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($chairs as $item): ?>
                                <?php 
                                    $item_damaged = $damaged_items[$item['item_id']] ?? 0;
                                ?>
                                <tr>
                                    <td class="item-name-cell">
                                        <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                                        <div class="stock-info">
                                            Total: <?= $item['total_stock'] ?> units
                                            <?php if($item_damaged > 0): ?>
                                                <br><span style="color: #dc2626;"><i class="fas fa-tools"></i> <?= $item_damaged ?> in repair</span>
                                            <?php endif; ?>
                                            <br>₱<?= number_format($item['individual_cost'], 2) ?>/day
                                        </div>
                                    </td>
                                    <?php foreach($dates as $date): 
                                        $avail = $availability_matrix[$item['item_id']]['availability'][$date];
                                    ?>
                                        <td>
                                            <div class="availability-badge status-<?= $avail['status'] ?>">
                                                <?= $avail['available'] ?> / <?= $item['total_stock'] ?>
                                            </div>
                                            <?php if($avail['rented'] > 0): ?>
                                                <div class="rented-count">
                                                    <i class="fas fa-calendar-check"></i> <?= $avail['rented'] ?> rented
                                                </div>
                                            <?php endif; ?>
                                            <?php if($avail['damaged'] > 0): ?>
                                                <div class="damaged-count">
                                                    <i class="fas fa-tools"></i> <?= $avail['damaged'] ?> in repair
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- TABLES SECTION -->
            <div style="margin-top: 30px;">
                <h4 class="section-title">
                    <i class="fas fa-table"></i> Tables Availability
                </h4>
                <div class="table-responsive">
                    <table class="availability-calendar">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <?php foreach($dates as $date): ?>
                                    <th><?= date('D, M j', strtotime($date)) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tables as $item): ?>
                                <?php 
                                    $item_damaged = $damaged_items[$item['item_id']] ?? 0;
                                ?>
                                <tr>
                                    <td class="item-name-cell">
                                        <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                                        <div class="stock-info">
                                            Total: <?= $item['total_stock'] ?> units
                                            <?php if($item_damaged > 0): ?>
                                                <br><span style="color: #dc2626;"><i class="fas fa-tools"></i> <?= $item_damaged ?> in repair</span>
                                            <?php endif; ?>
                                            <br>₱<?= number_format($item['individual_cost'], 2) ?>/day
                                        </div>
                                    </td>
                                    <?php foreach($dates as $date): 
                                        $avail = $availability_matrix[$item['item_id']]['availability'][$date];
                                    ?>
                                        <td>
                                            <div class="availability-badge status-<?= $avail['status'] ?>">
                                                <?= $avail['available'] ?> / <?= $item['total_stock'] ?>
                                            </div>
                                            <?php if($avail['rented'] > 0): ?>
                                                <div class="rented-count">
                                                    <i class="fas fa-calendar-check"></i> <?= $avail['rented'] ?> rented
                                                </div>
                                            <?php endif; ?>
                                            <?php if($avail['damaged'] > 0): ?>
                                                <div class="damaged-count">
                                                    <i class="fas fa-tools"></i> <?= $avail['damaged'] ?> in repair
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>