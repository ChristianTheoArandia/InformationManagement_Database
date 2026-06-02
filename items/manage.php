<?php
require_once '../includes/database.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    
    // Check if item is used in any transaction
    $check = $conn->query("SELECT COUNT(*) as count FROM Transaction_Item WHERE item_id = '$item_id'");
    $used = $check->fetch_assoc()['count'];
    
    if ($used > 0) {
        $error = "Cannot delete this item because it has been used in transactions.";
    } else {
        $stmt = $conn->prepare("DELETE FROM Rental_Item WHERE item_id = ?");
        $stmt->bind_param("s", $item_id);
        if ($stmt->execute()) {
            $success = "Item deleted successfully!";
        } else {
            $error = "Error deleting item: " . $conn->error;
        }
    }
}

// Get chairs only (item_type_id = '001')
$chairs = $conn->query("SELECT * FROM Rental_Item WHERE item_type_id = '001' ORDER BY item_id");

// Get tables only (item_type_id = '002')
$tables = $conn->query("SELECT * FROM Rental_Item WHERE item_type_id = '002' ORDER BY item_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Table & Chair Rental</title>
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
        
        .inventory-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        /* Header section */
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
        
        /* Section titles for Chairs and Tables */
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #374151;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }
        
        .section-title i {
            color: #667eea;
            margin-right: 10px;
        }
        
        /* Buttons */
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
            color: white;
        }
        
        .btn-edit {
            background: #f59e0b;
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 5px;
        }
        
        .btn-edit:hover {
            background: #d97706;
            color: white;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-delete:hover {
            background: #dc2626;
            color: white;
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
            border: none;
        }
        
        .btn-back:hover {
            background: #4b5563;
            color: white;
            transform: translateX(-3px);
        }
        
        /* Table styles */
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .inventory-table th {
            background: #f8f9fa;
            padding: 14px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: #555;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .inventory-table td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
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
        
        .stock-badge {
            background: #dcfce7;
            color: #166534;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }
        
        .low-stock {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="inventory-card">
            <!-- Header with Title and Add Button -->
            <div class="header-section">
                <h3>
                    <i class="fas fa-boxes"></i> Inventory Management
                </h3>
                <a href="add_item.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add Item
                </a>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if(isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- CHAIRS SECTION -->
            <div>
                <h4 class="section-title">
                    <i class="fas fa-chair"></i> Chairs
                </h4>
                <?php if($chairs && $chairs->num_rows > 0): ?>
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Cost / Day</th>
                                <th>Stock</th>
                                <th width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $chairs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item_id']) ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td>₱<?= number_format($item['individual_cost'], 2) ?></td>
                                <td>
                                    <span class="stock-badge <?= $item['total_stock'] < 5 ? 'low-stock' : '' ?>">
                                        <?= $item['total_stock'] ?> units
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage.php?delete=<?= $item['item_id'] ?>" class="btn-delete" 
                                       onclick="return confirm('Delete this item?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chair"></i>
                        <p>No chairs found</p>
                        <a href="add_item.php" class="btn-add" style="display: inline-flex; margin-top: 10px; padding: 8px 20px;">
                            <i class="fas fa-plus"></i> Add Chair
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- TABLES SECTION -->
            <div>
                <h4 class="section-title">
                    <i class="fas fa-table"></i> Tables
                </h4>
                <?php if($tables && $tables->num_rows > 0): ?>
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Cost / Day</th>
                                <th>Stock</th>
                                <th width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $tables->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item_id']) ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td>₱<?= number_format($item['individual_cost'], 2) ?></td>
                                <td>
                                    <span class="stock-badge <?= $item['total_stock'] < 5 ? 'low-stock' : '' ?>">
                                        <?= $item['total_stock'] ?> units
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage.php?delete=<?= $item['item_id'] ?>" class="btn-delete" 
                                       onclick="return confirm('Delete this item?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-table"></i>
                        <p>No tables found</p>
                        <a href="add_item.php" class="btn-add" style="display: inline-flex; margin-top: 10px; padding: 8px 20px;">
                            <i class="fas fa-plus"></i> Add Table
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Back to Dashboard Button -->
            <div style="margin-top: 20px;">
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>