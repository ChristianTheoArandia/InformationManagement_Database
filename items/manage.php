<?php
require_once '../includes/database.php';

// Handle delete request
if (isset($_GET['delete'])) {

    $item_id = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM Rental_Item WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();

    header("Location: manage.php");
    exit();
}

$items = $conn->query("
    SELECT ri.*, it.type_name
    FROM Rental_Item ri
    LEFT JOIN Item_Type it ON ri.item_type_id = it.item_type_id
    ORDER BY ri.item_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inventory Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#f1f3f6;
    padding:40px;
}

.container{
    width:100%;
}

.page-card{
    background:white;
    border-radius:30px;
    padding:45px;
    box-shadow:0 8px 30px rgba(0,0,0,.05);
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
}

.page-header h2{
    font-size:2.2rem;
    font-weight:700;
    color:#1f2937;
}

.page-header i{
    color:#667eea;
    margin-right:10px;
}

.btn-add{
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:white;
    text-decoration:none;
    padding:16px 28px;
    border-radius:16px;
    font-weight:600;
}

.btn-add:hover{
    opacity:.95;
    color:white;
}

table{
    width:100%;
    border-collapse:collapse;
}

thead th{
    background:#f3f4f6;
    padding:20px;
    text-align:left;
    color:#374151;
    font-weight:600;
}

tbody td{
    padding:22px 20px;
    border-top:1px solid #e5e7eb;
}

.stock-badge{
    background:#dcfce7;
    color:#166534;
    padding:8px 14px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
}

.low-stock{
    background:#fee2e2;
    color:#dc2626;
}

.btn-edit{
    background:#f59e0b;
    color:white;
    text-decoration:none;
    padding:8px 15px;
    border-radius:12px;
    margin-right:8px;
}

.btn-edit:hover{
    color:white;
}

.btn-delete{
    background:#ef4444;
    color:white;
    text-decoration:none;
    padding:8px 15px;
    border-radius:12px;
}

.btn-delete:hover{
    color:white;
}

.btn-back{
    display:inline-block;
    margin-top:35px;
    background:#6b7280;
    color:white;
    text-decoration:none;
    padding:16px 28px;
    border-radius:16px;
}

.btn-back:hover{
    color:white;
}

.table-responsive{
    overflow-x:auto;
}
</style>
</head>
<body>

<div class="container">

    <div class="page-card">

        <div class="page-header">
            <h2>
                <i class="fas fa-boxes"></i>
                Inventory Management
            </h2>

            <a href="add_item.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Add Item
            </a>
        </div>

        <div class="table-responsive">

            <table>

                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>Cost / Day</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($item = $items->fetch_assoc()): ?>

                    <tr>

                        <td><?= $item['item_id'] ?></td>

                        <td><?= htmlspecialchars($item['item_name']) ?></td>

                        <td><?= htmlspecialchars($item['type_name'] ?? 'Uncategorized') ?></td>

                        <td>
                            ₱<?= number_format($item['individual_cost'], 2) ?>
                        </td>

                        <td>
                            <span class="stock-badge <?= $item['total_stock'] < 5 ? 'low-stock' : '' ?>">
                                <?= $item['total_stock'] ?> units
                            </span>
                        </td>

                        <td>

                            <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <a href="manage.php?delete=<?= $item['item_id'] ?>"
                               class="btn-delete"
                               onclick="return confirm('Delete this item?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

        <a href="../index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

    </div>

</div>

</body>
</html>