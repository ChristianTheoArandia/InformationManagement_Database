<?php
require_once '../includes/database.php';

// Get item types for dropdown
$itemTypes = $conn->query("SELECT * FROM Item_Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = generateId('I', 'Rental_Item', 'item_id');
    $item_name = $_POST['item_name'];
    $item_type_id = $_POST['item_type_id'];
    $individual_cost = $_POST['individual_cost'];
    $total_stock = $_POST['total_stock'];
    
    $sql = "INSERT INTO Rental_Item (item_id, item_name, item_type_id, individual_cost, total_stock) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $item_id, $item_name, $item_type_id, $individual_cost, $total_stock);
    
    if ($stmt->execute()) {
        $success = "Item added successfully! ID: $item_id";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Rental Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4><i class="fas fa-plus-circle"></i> Add Rental Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Item Name</label>
                                <input type="text" name="item_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Type</label>
                                <select name="item_type_id" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <?php while($type = $itemTypes->fetch_assoc()): ?>
                                        <option value="<?= $type['item_type_id'] ?>">
                                            <?= $type['type_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Daily Cost (₱)</label>
                                <input type="number" name="individual_cost" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Stock</label>
                                <input type="number" name="total_stock" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Item</button>
                            <a href="manage.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>