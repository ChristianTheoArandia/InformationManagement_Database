<?php
require_once '../includes/database.php';

$employee_id = $_GET['id'] ?? '';
$result = $conn->query("SELECT * FROM Employee WHERE employee_id = '$employee_id'");
$employee = $result->fetch_assoc();

if (!$employee) {
    header("Location: list.php?error=Employee not found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $wage = $_POST['wage'];
    
    $stmt = $conn->prepare("UPDATE Employee SET first_name=?, last_name=?, wage=? WHERE employee_id=?");
    $stmt->bind_param("ssis", $first_name, $last_name, $wage, $employee_id);
    
    if ($stmt->execute()) {
        header("Location: list.php?success=Employee updated successfully");
        exit();
    } else {
        $error = "Error updating employee: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Table & Chair Rental</title>
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
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
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
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h3>
                <i class="fas fa-user-edit"></i> Edit Employee
            </h3>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">FIRST NAME</label>
                    <input type="text" name="first_name" class="form-control" 
                           value="<?= htmlspecialchars($employee['first_name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">LAST NAME</label>
                    <input type="text" name="last_name" class="form-control" 
                           value="<?= htmlspecialchars($employee['last_name']) ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">DAILY WAGE (₱)</label>
                    <input type="number" name="wage" class="form-control" 
                           value="<?= $employee['wage'] ?>" step="0.01" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-update">
                        <i class="fas fa-save"></i> Update Employee
                    </button>
                    <a href="list.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>