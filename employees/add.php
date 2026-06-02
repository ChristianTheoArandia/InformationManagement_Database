<?php
require_once '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = generateId('E', 'Employee', 'employee_id');
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $wage = $_POST['wage'];
    
    $stmt = $conn->prepare("INSERT INTO Employee (employee_id, first_name, last_name, wage) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $employee_id, $first_name, $last_name, $wage);
    
    if ($stmt->execute()) {
        $success = "Employee added successfully! ID: $employee_id";
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
    <title>Add Employee - Table & Chair Rental</title>
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
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #cbd5e1;
            font-size: 13px;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel:hover {
            background: #d97706;
            color: white;
        }
        
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
                <i class="fas fa-user-tie"></i> Add New Employee
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
                    <label class="form-label">FIRST NAME</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">LAST NAME</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">DAILY WAGE (₱)</label>
                    <input type="number" name="wage" class="form-control" placeholder="0.00" step="0.01" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Employee
                    </button>
                    <a href="list.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
            
            <div>
                <a href="list.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Employee List
                </a>
            </div>
        </div>
    </div>
</body>
</html>