<?php
require_once '../includes/database.php';
session_start();

$clients = $conn->query("SELECT * FROM Client ORDER BY client_id");
$employees = $conn->query("SELECT * FROM Employee ORDER BY employee_id");
$items = $conn->query("SELECT * FROM Rental_Item WHERE total_stock > 0");

$cart = $_SESSION['cart'] ?? [];

// Handle Add Client from this page
$showAddClientForm = isset($_GET['add_client']);
$clientSuccess = '';
$clientError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add_client'])) {
    $result = $conn->query("SELECT MAX(client_id) as max_id FROM Client");
    $row = $result->fetch_assoc();
    $lastId = $row['max_id'];
    if ($lastId) {
        $num = intval(substr($lastId, 1)) + 1;
        $client_id = 'C' . str_pad($num, 5, '0', STR_PAD_LEFT);
    } else {
        $client_id = 'C00001';
    }
    
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact = $_POST['contact'];
    $location = $_POST['location'];
    
    $stmt = $conn->prepare("INSERT INTO Client (client_id, first_name, last_name, contact, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $client_id, $first_name, $last_name, $contact, $location);
    
    if ($stmt->execute()) {
        $clientSuccess = "Client added successfully! ID: $client_id";
        $clients = $conn->query("SELECT * FROM Client ORDER BY client_id");
    } else {
        $clientError = "Error: " . $conn->error;
    }
}

// Handle AJAX add to cart
if (isset($_POST['ajax_add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $cart[$item_id] = ($cart[$item_id] ?? 0) + $quantity;
    $_SESSION['cart'] = $cart;
    
    // Calculate new total
    $newTotal = 0;
    foreach ($cart as $id => $qty) {
        $item = $conn->query("SELECT individual_cost FROM Rental_Item WHERE item_id = '$id'")->fetch_assoc();
        if ($item) {
            $newTotal += $item['individual_cost'] * $qty;
        }
    }
    
    echo json_encode(['success' => true, 'total' => $newTotal]);
    exit();
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $transaction_id = generateId('T', 'TransactionTbl', 'transaction_id');
    $client_id = $_POST['client_id'];
    $employee_id = $_POST['employee_id'];
    $start_date = $_POST['start_date'];
    $return_date = $_POST['return_date'];
    
    $stmt = $conn->prepare("INSERT INTO TransactionTbl (transaction_id, client_id, employee_id, transaction_date, start_date, return_date) VALUES (?, ?, ?, CURDATE(), ?, ?)");
    $stmt->bind_param("sssss", $transaction_id, $client_id, $employee_id, $start_date, $return_date);
    
    if ($stmt->execute()) {
        foreach ($cart as $item_id => $quantity) {
            $conn->query("INSERT INTO Transaction_Item (transaction_id, item_id, quantity) VALUES ('$transaction_id', '$item_id', $quantity)");
        }
        unset($_SESSION['cart']);
        header("Location: view.php?id=$transaction_id");
        exit();
    }
}

// Calculate total
$total = 0;
$cartItems = [];
foreach ($cart as $item_id => $qty) {
    $item = $conn->query("SELECT * FROM Rental_Item WHERE item_id = '$item_id'")->fetch_assoc();
    if ($item) {
        $cartItems[] = ['item' => $item, 'qty' => $qty];
        $total += $item['individual_cost'] * $qty;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Rental Transaction - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/sidebar.css">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; margin: 0; }
        .main-content {
            margin-left: 280px;
            padding: 25px 30px;
            min-height: 100vh;
        }
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .transaction-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            height: 100%;
        }
        
        .cart-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            height: 100%;
            position: sticky;
            top: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
            outline: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
        }
        
        .btn-add-client {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 500;
            font-size: 13px;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-add-client:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .client-select-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .client-select-group .form-select {
            flex: 1;
        }
        
        .quick-add-card {
            background: #fef3c7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
        }
        
        .cart-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            background: #eef2ff;
            transform: translateX(5px);
        }
        
        .total-amount {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
        
        .btn-remove {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 12px;
        }
        
        .btn-remove:hover {
            background: #dc2626;
        }
        
        .alert {
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .grid-2col .form-control {
            margin: 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?> 
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1" style="font-weight: 700;"><i class="fas fa-shopping-cart text-primary"></i> New Rental Transaction</h2>
                    <p class="text-muted mb-0">Create a new rental order for your customer</p>
                </div>
                <a href="../index.php" style="background: #6b7280; color: white; padding: 10px 24px; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="row">
            <!-- Left Column - Transaction Details -->
            <div class="col-lg-7">
                <div class="transaction-card">
                    <h5 class="section-title">
                        <i class="fas fa-file-alt text-primary"></i> Transaction Details
                    </h5>
                    
                    <!-- Quick Add Client Form -->
                    <?php if($showAddClientForm): ?>
                        <div class="quick-add-card">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <strong><i class="fas fa-user-plus"></i> Add New Client</strong>
                                <a href="create.php" style="background: #6b7280; color: white; border: none; border-radius: 8px; padding: 6px 14px; font-size: 12px; text-decoration: none;">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                            <form method="POST">
                                <div class="grid-2col">
                                    <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                                    <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                                    <input type="text" name="contact" class="form-control" placeholder="Contact Number" required>
                                    <input type="text" name="location" class="form-control" placeholder="Location">
                                </div>
                                <button type="submit" name="quick_add_client" class="btn btn-primary w-100" style="padding: 10px;">
                                    <i class="fas fa-save"></i> Save Client
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Success/Error Messages -->
                    <?php if($clientSuccess): ?>
                        <div class="alert alert-success"><?= $clientSuccess ?></div>
                    <?php endif; ?>
                    <?php if($clientError): ?>
                        <div class="alert alert-danger"><?= $clientError ?></div>
                    <?php endif; ?>
                    
                    <!-- Main Transaction Form -->
                    <form method="POST" id="transactionForm">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user"></i> CLIENT
                            </label>
                            <div class="client-select-group">
                                <select name="client_id" class="form-select" required>
                                    <option value="">Select Client</option>
                                    <?php 
                                    $clients = $conn->query("SELECT * FROM Client ORDER BY client_id");
                                    while($c = $clients->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $c['client_id'] ?>">
                                            <?= $c['first_name'] . ' ' . $c['last_name'] ?> (<?= $c['client_id'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <a href="?add_client=1" class="btn-add-client">
                                    <i class="fas fa-plus"></i> New Client
                                </a>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-tie"></i> EMPLOYEE (HANDLED BY)
                                </label>
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select Employee</option>
                                    <?php 
                                    $employees = $conn->query("SELECT * FROM Employee ORDER BY employee_id");
                                    while($e = $employees->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $e['employee_id'] ?>">
                                            <?= $e['first_name'] . ' ' . $e['last_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt"></i> RENTAL START DATE
                                </label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-check"></i> RETURN DATE
                                </label>
                                <input type="date" name="return_date" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5 class="section-title">
                                <i class="fas fa-plus-circle text-success"></i> Add Items to Cart
                            </h5>
                            <div class="row">
                                <div class="col-md-7 mb-2">
                                    <select id="itemSelect" class="form-select">
                                        <option value="">Select Item</option>
                                        <?php 
                                        $items = $conn->query("SELECT * FROM Rental_Item WHERE total_stock > 0");
                                        while($i = $items->fetch_assoc()): 
                                        ?>
                                            <option value="<?= $i['item_id'] ?>" data-cost="<?= $i['individual_cost'] ?>">
                                                <?= $i['item_name'] ?> - ₱<?= number_format($i['individual_cost'], 2) ?>/day
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="number" id="quantity" class="form-control" placeholder="Qty" value="1" min="1">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="button" class="btn btn-primary w-100" onclick="addToCart()">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Right Column - Shopping Cart -->
            <div class="col-lg-5">
                <div class="cart-card">
                    <h5 class="section-title">
                        <i class="fas fa-shopping-cart text-warning"></i> Shopping Cart
                    </h5>
                    
                    <div id="cartItemsList">
                        <?php if(empty($cartItems)): ?>
                            <div class="empty-cart">
                                <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                                <p>Cart is empty</p>
                                <small>Add items from the left panel</small>
                            </div>
                        <?php else: ?>
                            <?php foreach($cartItems as $item): ?>
                                <div class="cart-item" data-item-id="<?= $item['item']['item_id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= $item['item']['item_name'] ?></strong><br>
                                            <small>₱<?= number_format($item['item']['individual_cost'], 2) ?> x <?= $item['qty'] ?></small>
                                        </div>
                                        <div>
                                            <strong class="text-primary">₱<?= number_format($item['item']['individual_cost'] * $item['qty'], 2) ?></strong>
                                            <button class="btn-remove ms-2" onclick="removeItem('<?= $item['item']['item_id'] ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>Estimated Total:</strong>
                        <span class="total-amount" id="cartTotal">₱<?= number_format($total, 2) ?></span>
                    </div>
                    
                    <button type="submit" form="transactionForm" name="checkout" class="btn btn-success" <?= empty($cartItems) ? 'disabled' : '' ?> id="checkoutBtn">
                        <i class="fas fa-check-circle"></i> Complete Transaction
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function addToCart() {
            let select = document.getElementById('itemSelect');
            let itemId = select.value;
            let quantity = document.getElementById('quantity').value;
            
            if(!itemId) {
                alert('Please select an item');
                return;
            }
            
            // AJAX request to add to cart without page refresh
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax_add_to_cart=1&item_id=' + itemId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Reload the page to show updated cart
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function removeItem(itemId) {
            // For remove, we still need a form submission since it's more complex
            let form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="remove_item" value="${itemId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>