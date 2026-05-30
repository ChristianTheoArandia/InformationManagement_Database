<?php
require_once '../includes/database.php';
$employee_id = $_GET['id'] ?? '';
$employee_id ? $conn->query("DELETE FROM Employee WHERE employee_id = '$employee_id'") : null;
header("Location: list.php?success=Employee deleted");
exit();
?>