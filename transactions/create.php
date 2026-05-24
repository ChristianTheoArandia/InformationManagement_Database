<?php
require_once '../includes/database.php';

// Get clients, employees, and items for dropdowns
$clients = $conn->query("SELECT * FROM Client ORDER BY client_id");
$employees = $conn->query("SELECT * FROM Employee ORDER BY employee_id");
$items = $conn->query("SELECT * FROM Rental_Item WHERE total_stock > 0");

$cart = $_SESSION['cart'] ?? [];
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        // Add item to cart
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        $cart[$item_id] = $quantity;
        $_SESSION['cart'] = $cart;
    } elseif (isset($_POST['checkout'])) {
        // Create transaction
        $transaction_id = generateId('T', 'TransactionTbl', 'transaction_id');
        $client_id = $_POST['client_id'];
        $employee_id = $_POST['employee_id'];
        $start_date = $_POST['start_date'];
        $return_date = $_POST['return_date'];
        
        $sql = "INSERT INTO TransactionTbl (transaction_id, client_id, employee_id, transaction_date, start_date, return_date) 
                VALUES (?, ?, ?, CURDATE(), ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $transaction_id, $client_id, $employee_id, $start_date, $return_date);
        
        if ($stmt->execute()) {
            // Add items to transaction
            foreach ($cart as $item_id => $quantity) {
                $conn->query("INSERT INTO Transaction_Item (transaction_id, item_id, quantity) 
                             VALUES ('$transaction_id', '$item_id', $quantity)");
            }
            unset($_SESSION['cart']);
            header("Location: view.php?id=$transaction_id");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Transaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><i class="fas fa-shopping-cart"></i> New Rental Transaction</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Transaction Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Client</label>
                                <select name="client_id" class="form-control" required>
                                    <option value="">Select Client</option>
                                    <?php while($c = $clients->fetch_assoc()): ?>
                                        <option value="<?= $c['client_id'] ?>">
                                            <?= $c['first_name'] . ' ' . $c['last_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    <?php while($e = $employees->fetch_assoc()): ?>
                                        <option value="<?= $e['employee_id'] ?>">
                                            <?= $e['first_name'] . ' ' . $e['last_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Return Date</label>
                                <input type="date" name="return_date" class="form-control" required>
                            </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>Add Items to Cart</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Select Item</label>
                            <select id="item_id" class="form-control">
                                <option value="">Choose Item</option>
                                <?php while($i = $items->fetch_assoc()): ?>
                                    <option value="<?= $i['item_id'] ?>" data-cost="<?= $i['individual_cost'] ?>">
                                        <?= $i['item_name'] ?> - ₱<?= $i['individual_cost'] ?>/day
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" id="quantity" class="form-control" min="1" value="1">
                        </div>
                        <button type="button" onclick="addToCart()" class="btn btn-primary">Add to Cart</button>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-warning">
                        <h5>Shopping Cart</h5>
                    </div>
                    <div class="card-body">
                        <div id="cartItems"></div>
                        <hr>
                        <button type="submit" name="checkout" class="btn btn-success w-100">Complete Transaction</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    let cart = [];
    
    function addToCart() {
        let itemSelect = document.getElementById('item_id');
        let itemId = itemSelect.value;
        let itemName = itemSelect.options[itemSelect.selectedIndex].text;
        let quantity = document.getElementById('quantity').value;
        
        if (!itemId) {
            alert('Please select an item');
            return;
        }
        
        cart.push({id: itemId, name: itemName, qty: quantity});
        displayCart();
    }
    
    function displayCart() {
        let html = '<table class="table table-sm">';
        cart.forEach(item => {
            html += `<tr><td>${item.name}</td><td>x${item.qty}</td></tr>`;
        });
        html += '</table>';
        document.getElementById('cartItems').innerHTML = html;
    }
    </script>
</body>
</html>