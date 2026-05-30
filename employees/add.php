<?php
require_once '../includes/database.php';

function generateId($prefix, $table, $column) {
    global $conn;
    $result = $conn->query("SELECT $column FROM $table ORDER BY $column DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $lastId = $result->fetch_assoc()[$column];
        $num = (int)substr($lastId, 1) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = generateId('E', 'Employee', 'employee_id');
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $wage = $_POST['wage'];
    
    $stmt = $conn->prepare("INSERT INTO Employee VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $employee_id, $first_name, $last_name, $wage);
    
    if ($stmt->execute()) {
        header("Location: list.php?success=Employee added successfully! ID: $employee_id");
        exit();
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
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; }
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 550px;
            margin: 0 auto;
        }
        .form-label { font-weight: 500; color: #333; margin-bottom: 8px; }
        .form-control { border-radius: 10px; border: 1px solid #e0e0e0; padding: 12px 15px; }
        .btn-save { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 500; }
        .btn-cancel { background: #6b7280; color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="form-card">
            <h3 class="mb-4"><i class="fas fa-user-tie text-success"></i> Add Employee</h3>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
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
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Employee</button>
                    <button type="button" class="btn-cancel" onclick="location.href='list.php'"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>