<?php
require_once '../includes/database.php';

// Get item types for dropdown
$itemTypes = $conn->query("SELECT * FROM Item_Type");

// Get item ID from URL
$item_id = $_GET['id'] ?? '';

if (!$item_id) {
    header("Location: manage.php?error=No item selected");
    exit();
}

// Get item data
$itemResult = $conn->query("SELECT * FROM Rental_Item WHERE item_id = '$item_id'");
$item = $itemResult->fetch_assoc();

if (!$item) {
    header("Location: manage.php?error=Item not found");
    exit();
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $item_type_id = $_POST['item_type_id'];
    $individual_cost = $_POST['individual_cost'];
    $total_stock = $_POST['total_stock'];
    
    $stmt = $conn->prepare("UPDATE Rental_Item SET item_name=?, item_type_id=?, individual_cost=?, total_stock=? WHERE item_id=?");
    $stmt->bind_param("ssiis", $item_name, $item_type_id, $individual_cost, $total_stock, $item_id);
    
    if ($stmt->execute()) {
        header("Location: manage.php?success=Item updated successfully");
        exit();
    } else {
        $error = "Error updating item: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f0f2f5;
            min-height: 100vh;
            padding: 50px 20px;
        }
        
        .form-container {
            max-width: 550px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .form-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f59e0b;
            display: inline-block;
        }
        
        .form-card h3 i {
            color: #f59e0b;
            margin-right: 10px;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
            outline: none;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245,158,11,0.3);
        }
        
        .btn-cancel {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #4b5563;
            color: white;
        }
        
        .alert {
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h3>
                <i class="fas fa-edit"></i> Edit Item
            </h3>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">ITEM ID</label>
                    <input type="text" class="form-control" value="<?= $item['item_id'] ?>" disabled>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">ITEM NAME</label>
                    <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($item['item_name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">TYPE</label>
                    <select name="item_type_id" class="form-select" required>
                        <option value="">Select Type</option>
                        <?php while($type = $itemTypes->fetch_assoc()): ?>
                            <option value="<?= $type['item_type_id'] ?>" <?= $type['item_type_id'] == $item['item_type_id'] ? 'selected' : '' ?>>
                                <?= $type['type_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">COST PER DAY (₱)</label>
                    <input type="number" step="0.01" name="individual_cost" class="form-control" value="<?= $item['individual_cost'] ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">TOTAL STOCK</label>
                    <input type="number" name="total_stock" class="form-control" value="<?= $item['total_stock'] ?>" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-update">
                        <i class="fas fa-save"></i> Update Item
                    </button>
                    <a href="manage.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>