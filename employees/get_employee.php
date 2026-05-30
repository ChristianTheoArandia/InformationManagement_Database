<?php
require_once '../includes/database.php';
header('Content-Type: application/json');
$employee_id = $_GET['id'] ?? '';
$result = $conn->query("SELECT * FROM Employee WHERE employee_id = '$employee_id'");
echo $result && $result->num_rows > 0 ? json_encode(['success' => true, 'data' => $result->fetch_assoc()]) : json_encode(['success' => false]);
?>