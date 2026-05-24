<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$search = $_GET['search'] ?? '';

$sql = "SELECT 
            c.client_id,
            CONCAT(c.first_name, ' ', c.last_name) as client_name,
            COUNT(DISTINCT t.transaction_id) as total_transactions,
            SUM(p.amount) as total_spent
        FROM Client c
        LEFT JOIN TransactionTbl t ON c.client_id = t.client_id
        LEFT JOIN Payment p ON t.transaction_id = p.transaction_id
        WHERE c.first_name LIKE '%$search%' 
           OR c.last_name LIKE '%$search%' 
           OR c.client_id LIKE '%$search%'
           OR c.contact LIKE '%$search%'
        GROUP BY c.client_id
        LIMIT 10";

$result = $conn->query($sql);
$data = [];

while($client = $result->fetch_assoc()) {
    // Get transactions for this client
    $transSql = "SELECT 
                    t.transaction_id,
                    t.transaction_date,
                    COUNT(ti.item_id) as items_count,
                    COALESCE(p.amount, 0) as amount,
                    CASE WHEN t.return_date < CURDATE() THEN 'Completed' ELSE 'Active' END as status
                FROM TransactionTbl t
                LEFT JOIN Transaction_Item ti ON t.transaction_id = ti.transaction_id
                LEFT JOIN Payment p ON t.transaction_id = p.transaction_id
                WHERE t.client_id = '{$client['client_id']}'
                GROUP BY t.transaction_id
                ORDER BY t.transaction_date DESC";
    
    $transResult = $conn->query($transSql);
    $transactions = [];
    while($trans = $transResult->fetch_assoc()) {
        $transactions[] = $trans;
    }
    
    $client['transactions'] = $transactions;
    $data[] = $client;
}

echo json_encode($data);
?>