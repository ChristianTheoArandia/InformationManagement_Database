<?php
require_once '../includes/database.php';
$employee_id = $_GET['id'] ?? '';
$result = $conn->query("SELECT * FROM Employee WHERE employee_id = '$employee_id'");
$employee = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $wage = $_POST['wage'];
    $stmt = $conn->prepare("UPDATE Employee SET first_name=?, last_name=?, wage=? WHERE employee_id=?");
    $stmt->bind_param("ssis", $first_name, $last_name, $wage, $employee_id);
    $stmt->execute() ? header("Location: list.php?success=Employee updated") : $error = "Update failed";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Poppins',sans-serif;}body{background:#f0f2f5;}
        .form-card{background:white;border-radius:20px;padding:30px;max-width:550px;margin:0 auto;}
        .btn-update{background:#3b82f6;color:white;border:none;padding:12px 30px;border-radius:10px;}
        .btn-cancel{background:#6b7280;color:white;border:none;padding:12px 30px;border-radius:10px;}
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="form-card">
        <h3><i class="fas fa-user-edit"></i> Edit Employee</h3>
        <form method="POST">
            <div class="mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control" value="<?= $employee['first_name'] ?>" required></div>
            <div class="mb-3"><label>Last Name</label><input type="text" name="last_name" class="form-control" value="<?= $employee['last_name'] ?>" required></div>
            <div class="mb-3"><label>Daily Wage</label><input type="number" name="wage" class="form-control" value="<?= $employee['wage'] ?>" step="0.01" required></div>
            <button type="submit" class="btn-update">Update</button>
            <a href="list.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>