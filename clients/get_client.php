<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? '';
$result = $conn->query("SELECT * FROM Client WHERE client_id = '$id'");
echo json_encode($result->fetch_assoc());
?>