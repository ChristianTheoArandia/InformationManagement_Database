<?php
require_once '../includes/database.php';
$result = $conn->query("SELECT * FROM Employee ORDER BY employee_id DESC");
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
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; }
        .table-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .btn-add { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; }
        .btn-edit { background: #3b82f6; color: white; border: none; padding: 5px 12px; border-radius: 8px; font-size: 12px; }
        .btn-delete { background: #ef4444; color: white; border: none; padding: 5px 12px; border-radius: 8px; font-size: 12px; }
        table th { background: #f8f9fa; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-user-tie text-success"></i> Employee List</h3>
                <a href="add.php" class="btn-add"><i class="fas fa-plus"></i> Add Employee</a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Employee ID</th><th>First Name</th><th>Last Name</th><th>Daily Wage (₱)</th><th>Actions</th</tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $row['employee_id'] ?></strong></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td>₱ <?= number_format($row['wage'], 2) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['employee_id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?= $row['employee_id'] ?>" class="btn-delete" onclick="return confirm('Delete this employee?')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>