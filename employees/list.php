<?php
require_once '../includes/database.php';

// Get all employees
$result = $conn->query("SELECT * FROM Employee ORDER BY employee_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><i class="fas fa-user-tie"></i> Employee List</h4>
            </div>
            <div class="card-body">
                <a href="add.php" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Add New Employee
                </a>
                <a href="../index.php" class="btn btn-secondary mb-3">
                    <i class="fas fa-home"></i> Back to Dashboard
                </a>
                
                <?php if($result && $result->num_rows > 0): ?>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Daily Wage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['employee_id'] ?></td>
                                <td><?= htmlspecialchars($row['first_name']) ?></td>
                                <td><?= htmlspecialchars($row['last_name']) ?></td>
                                <td>₱<?= number_format($row['wage'], 2) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $row['employee_id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?= $row['employee_id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this employee?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No employees found. 
                        <a href="add.php">Click here to add your first employee</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>