<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$client_id = $_POST['client_id'];
$sql = "DELETE FROM Client WHERE client_id='$client_id'";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>