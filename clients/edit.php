<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$client_id = $_POST['client_id'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$contact = $_POST['contact'];
$location = $_POST['location'];

$sql = "UPDATE Client SET first_name='$first_name', last_name='$last_name', contact='$contact', location='$location' WHERE client_id='$client_id'";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>