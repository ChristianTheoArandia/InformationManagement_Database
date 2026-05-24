<?php
require_once '../includes/database.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    $conn->query("DELETE FROM Rental_Item WHERE item_id = '$item_id'");
    header("Location: manage.php");
    exit();
}

// Get all items with their types
$result = $conn->query("
    SELECT ri.*, it.type_name 
    FROM Rental_Item ri
    JOIN Item_Type it ON ri.item_type_id = it.item_type_id
    ORDER BY ri.item_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Rental Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><i class="fas fa-box"></i> Manage Rental Items</h4>
            </div>
            <div class="card-body">
                <a href="add_item.php" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Add New Item
                </a>
                
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Daily Cost</th>
                            <th>Total Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['item_id'] ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= $row['type_name'] ?></td>
                            <td>₱<?= number_format($row['individual_cost'], 2) ?></td>
                            <td><?= $row['total_stock'] ?></td>
                            <td>
                                <a href="edit_item.php?id=<?= $row['item_id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?= $row['item_id'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this item?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>