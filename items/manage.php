<?php
require_once '../includes/database.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    
    // Check if item is used in any transaction
    $check = $conn->query("SELECT COUNT(*) as count FROM Transaction_Item WHERE item_id = '$item_id'");
    $used = $check->fetch_assoc()['count'];
    
    if ($used > 0) {
        $error = "Cannot delete this item because it is still on an active transaction or transaction history.";
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
}

/* Sidebar Styles */
.sidebar {
    width: 280px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 25px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h4 {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin-top: 10px;
    line-height: 1.3;
}

.sidebar-header i {
    font-size: 40px;
    color: white;
}

.sidebar-menu {
    list-style: none;
    padding: 20px 15px;
}

.sidebar-menu li {
    margin-bottom: 8px;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s;
    font-size: 14px;
    font-weight: 500;
}

.sidebar-menu li a:hover {
    background: rgba(255,255,255,0.15);
    color: white;
    transform: translateX(5px);
}

.sidebar-menu li.active a {
    background: rgba(255,255,255,0.2);
    color: white;
}

.sidebar-menu li a i {
    width: 28px;
    margin-right: 12px;
    font-size: 16px;
}

.main-content{
    margin-left: 280px;
    padding: 25px 30px;
    min-height: 100vh;
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

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #374151;
    margin: 30px 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 3px solid #667eea;
    display: inline-block;
}

.section-title i {
    margin-right: 10px;
    color: #667eea;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-bottom: 30px;
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

.alert {
    padding: 12px 20px;
    border-radius: 12px;
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

.empty-row td {
    text-align: center;
    padding: 30px;
    color: #9ca3af;
}
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">

    <div class="page-card">

        <div class="page-header">
            <h2>
                <i class="fas fa-boxes"></i>
                Inventory Management
            </h2>
            <a href="add_item.php" class="btn-add">
                <i class="fas fa-plus"></i> Add Item
            </a>
        </div>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- CHAIRS SECTION -->
        <div>
            <h3 class="section-title">
                <i class="fas fa-chair"></i> Chairs
            </h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Cost / Day</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($chairs && $chairs->num_rows > 0): ?>
                            <?php while($item = $chairs->fetch_assoc()): ?>
                            <tr>
                                <td><?= $item['item_id'] ?></td>
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
                                    <a href="manage.php?delete=<?= $item['item_id'] ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Delete this item?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="5">No chairs found. <a href="add_item.php">Add your first chair</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABLES SECTION -->
        <div>
            <h3 class="section-title">
                <i class="fas fa-table"></i> Tables
            </h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Cost / Day</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($tables && $tables->num_rows > 0): ?>
                            <?php while($item = $tables->fetch_assoc()): ?>
                            <tr>
                                <td><?= $item['item_id'] ?></td>
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
                                    <a href="manage.php?delete=<?= $item['item_id'] ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Delete this item?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="5">No tables found. <a href="add_item.php">Add your first table</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="../index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

    </div>

</div>

</body>
</html>