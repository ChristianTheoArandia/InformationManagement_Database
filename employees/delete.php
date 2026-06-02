<?php
require_once '../includes/database.php';

$employee_id = $_GET['id'] ?? '';

if ($employee_id) {
    // Check if employee has any transactions
    $check = $conn->query("SELECT COUNT(*) as count FROM TransactionTbl WHERE employee_id = '$employee_id'");
    $hasTransactions = $check->fetch_assoc()['count'];
    
    if ($hasTransactions > 0) {
        // Employee has transactions, cannot delete
        header("Location: list.php?error=Cannot delete employee with existing transaction records.");
        exit();
    } else {
        // Safe to delete
        $conn->query("DELETE FROM Employee WHERE employee_id = '$employee_id'");
        header("Location: list.php?success=Employee deleted successfully");
        exit();
    }
}

header("Location: list.php");
exit();
?>