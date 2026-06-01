<?php
require_once '../includes/database.php';

$result = $conn->query("SELECT * FROM Employee ORDER BY employee_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
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
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .header-section h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
        }
        .btn-edit {
            background: #f59e0b;
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            margin-right: 5px;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
        }
        /* ADD THIS NEW STYLE */
        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .alert { padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="header-section">
            <h3><i class="fas fa-user-tie"></i> Employee List</h3>
            <a href="add.php" class="btn-add"><i class="fas fa-plus"></i> Add Employee</a>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr><th>Employee ID</th><th>First Name</th><th>Last Name</th><th>Daily Wage</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['employee_id'] ?></td>
                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                    <td>₱<?= number_format($row['wage'], 2) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['employee_id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="list.php?delete=<?= $row['employee_id'] ?>" class="btn-delete" onclick="return confirm('Delete this employee?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- ADD THIS BACK BUTTON -->
        <div>
            <a href="../index.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <!-- END OF ADDED BUTTON -->
        
    </div>
</body>
</html>