<?php
require_once '../includes/database.php';

$itemTypes = $conn->query("SELECT * FROM Item_Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = generateId('I', 'Rental_Item', 'item_id');
    $item_name = $_POST['item_name'];
    $item_type_id = $_POST['item_type_id'];
    $individual_cost = $_POST['individual_cost'];
    $total_stock = $_POST['total_stock'];
    
    $stmt = $conn->prepare("INSERT INTO Rental_Item (item_id, item_name, item_type_id, individual_cost, total_stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $item_id, $item_name, $item_type_id, $individual_cost, $total_stock);
    
    if ($stmt->execute()) {
        $success = "Item added successfully! ID: $item_id";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Table & Chair Rental</title>
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
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }
        
        .form-card h3 i {
            color: #667eea;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
            outline: none;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        
        .btn-cancel {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #d97706;
            color: white;
        }
        
        /* ======================================== */
        /* BACK TO INVENTORY BUTTON                 */
        /* ======================================== */
        .btn-back {
            background: #6b7280;
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            transition: all 0.3s;
            border: none;
            font-weight: 600;
        }
        
        .btn-back:hover {
            background: #4b5563;
            color: white;
            transform: translateX(-3px);
        }
        /* ======================================== */
        
        .alert {
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h3>
                <i class="fas fa-box"></i> Add New Item
            </h3>
            
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
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">ITEM NAME</label>
                    <input type="text" name="item_name" class="form-control" placeholder="e.g., Monobloc Chair" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">TYPE</label>
                    <select name="item_type_id" class="form-select" required>
                        <option value="">Select Type</option>
                        <?php while($type = $itemTypes->fetch_assoc()): ?>
                            <option value="<?= $type['item_type_id'] ?>"><?= $type['type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">COST PER DAY (₱)</label>
                    <input type="number" step="0.01" name="individual_cost" class="form-control" placeholder="0.00" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">TOTAL STOCK</label>
                    <input type="number" name="total_stock" class="form-control" placeholder="Quantity" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Item
                    </button>
                    <a href="manage.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
            
            <!-- ============================================= -->
            <!-- BACK TO INVENTORY BUTTON                       -->
            <!-- This takes you back to the inventory list      -->
            <!-- ============================================= -->
            <div class="action-buttons">
                <a href="manage.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
            <!-- ============================================= -->
            
        </div>
    </div>
</body>
</html>