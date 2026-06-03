<?php
require_once '../includes/database.php';

$transaction_id = $_GET['transaction_id'] ?? '';
$message = '';
$messageType = '';

if (!$transaction_id) {
    header("Location: ../transactions/list.php?error=No transaction selected");
    exit();
}

// Get transaction details with total amount
$transaction = $conn->query("
    SELECT t.*, SUM(ti.quantity * ri.individual_cost) as total_amount
    FROM TransactionTbl t
    JOIN Transaction_Item ti ON t.transaction_id = ti.transaction_id
    JOIN Rental_Item ri ON ti.item_id = ri.item_id
    WHERE t.transaction_id = '$transaction_id'
    GROUP BY t.transaction_id
")->fetch_assoc();

$totalAmount = $transaction['total_amount'] ?? 0;

// Get payment types (only Cash and Digital Wallet)
$paymentTypes = $conn->query("SELECT * FROM Payment_Type WHERE payment_type_id IN ('001', '003')");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = generateId('P', 'Payment', 'payment_id');
    $payment_type_id = $_POST['payment_type_id'];
    $amount = $totalAmount;
    $error = false;
    
    if ($payment_type_id == '003') {
        $wallet_type_id = $_POST['wallet_type_id'];
        $account_number = $_POST['account_number'];
        $reference_no = $_POST['reference_no'];
        
        if (!preg_match('/^\d{11}$/', $account_number)) {
            $message = "Account number must be exactly 11 digits!";
            $messageType = "danger";
            $error = true;
        }
        elseif ($wallet_type_id == '001' && !preg_match('/^\d{13}$/', $reference_no)) {
            $message = "GCash reference number must be exactly 13 digits!";
            $messageType = "danger";
            $error = true;
        }
        elseif ($wallet_type_id == '002' && !preg_match('/^\d{12}$/', $reference_no)) {
            $message = "PayMaya reference number must be exactly 12 digits!";
            $messageType = "danger";
            $error = true;
        }
    }
    
    if (!$error) {
        $sql = "INSERT INTO Payment (payment_id, transaction_id, payment_date, amount, payment_type_id) 
                VALUES (?, ?, CURDATE(), ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $payment_id, $transaction_id, $amount, $payment_type_id);
        
        if ($stmt->execute()) {
            if ($payment_type_id == '001') {
                $amount_received = $_POST['amount_received'];
                $change_amount = $amount_received - $amount;
                $conn->query("INSERT INTO Cash (payment_id, amount_received, change_amount) VALUES ('$payment_id', $amount_received, $change_amount)");
            }
            elseif ($payment_type_id == '003') {
                $conn->query("INSERT INTO Wallet (payment_id, wallet_type_id, account_number, transaction_reference_no) VALUES ('$payment_id', '$wallet_type_id', '$account_number', '$reference_no')");
            }
            
            $conn->query("UPDATE TransactionTbl SET payment_status = 'PAID' WHERE transaction_id = '$transaction_id'");
            header("Location: ../transactions/list.php?success=Payment recorded successfully");
            exit();
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; min-height: 100vh; padding: 50px 20px; }
        .payment-container { max-width: 550px; margin: 0 auto; }
        .payment-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .payment-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #10b981;
            display: inline-block;
        }
        .payment-card h3 i { color: #10b981; margin-right: 10px; }
        .form-label { font-weight: 600; color: #555; margin-bottom: 8px; font-size: 12px; letter-spacing: 0.5px; }
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
            outline: none;
        }
        .btn-payment {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 1px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }
        .btn-payment:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16,185,129,0.3); }
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 1px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
            width: 100%;
            margin-top: 10px;
        }
        .btn-back:hover { background: #4b5563; color: white; }
        .alert { border-radius: 12px; margin-bottom: 20px; }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .total-amount { font-size: 24px; font-weight: 700; color: #10b981; }
        .button-group { display: flex; flex-direction: column; gap: 10px; margin-top: 25px; }
        .mt-2 { margin-top: 5px; }
        .mb-2 { margin-bottom: 5px; }
        .mb-3-custom { margin-bottom: 20px; }
        .mt-4-custom { margin-top: 25px; }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h3><i class="fas fa-credit-card"></i> Process Payment</h3>
            
            <?php if($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <div class="d-flex justify-content-between mb-2">
                    <strong>Transaction ID:</strong>
                    <span><?= htmlspecialchars($transaction_id) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <strong>Total Amount Due:</strong>
                    <span class="total-amount">₱<?= number_format($totalAmount, 2) ?></span>
                </div>
            </div>
            
            <form method="POST" id="paymentForm">
                <div class="mb-3-custom">
                    <label class="form-label">PAYMENT METHOD</label>
                    <select name="payment_type_id" id="paymentType" class="form-select" required>
                        <option value="">Select Payment Method</option>
                        <?php while($pt = $paymentTypes->fetch_assoc()): ?>
                            <option value="<?= $pt['payment_type_id'] ?>"><?= $pt['type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Cash Fields -->
                <div id="cashFields" style="display:none;">
                    <div class="mb-3-custom">
                        <label class="form-label">AMOUNT RECEIVED</label>
                        <input type="number" name="amount_received" class="form-control" step="0.01" required>
                    </div>
                </div>
                
                <!-- Digital Wallet Fields -->
                <div id="walletFields" style="display:none;">
                    <div class="mb-3-custom">
                        <label class="form-label">WALLET TYPE</label>
                        <select name="wallet_type_id" id="walletType" class="form-select" required>
                            <option value="">Select Wallet</option>
                            <option value="001">GCash</option>
                            <option value="002">PayMaya</option>
                        </select>
                    </div>
                    <div class="mb-3-custom">
                        <label class="form-label">ACCOUNT NUMBER</label>
                        <input type="text" name="account_number" id="accountNumber" class="form-control" placeholder="Enter 11-digit account number" maxlength="11" required>
                    </div>
                    <div class="mb-3-custom">
                        <label class="form-label">REFERENCE NUMBER</label>
                        <input type="text" name="reference_no" id="referenceNo" class="form-control" placeholder="Enter reference number" required>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-payment">
                        <i class="fas fa-check-circle"></i> Process Payment
                    </button>
                    <a href="../transactions/list.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Transactions
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('walletType')?.addEventListener('change', function() {
            const refHint = document.getElementById('refHint');
            const refInput = document.getElementById('referenceNo');
            
            if (this.value === '001') {
                refHint.innerHTML = 'GCash: Must be exactly 13 digits';
                refInput.maxLength = 13;
                refInput.placeholder = 'Enter 13-digit reference number';
            } else if (this.value === '002') {
                refHint.innerHTML = 'PayMaya: Must be exactly 12 digits';
                refInput.maxLength = 12;
                refInput.placeholder = 'Enter 12-digit reference number';
            } else {
                refHint.innerHTML = 'GCash: 13 digits | PayMaya: 12 digits';
                refInput.maxLength = '';
                refInput.placeholder = 'Enter reference number';
            }
        });
        
        document.getElementById('paymentType').addEventListener('change', function() {
            document.getElementById('cashFields').style.display = 'none';
            document.getElementById('walletFields').style.display = 'none';
            
            if(this.value === '001') {
                document.getElementById('cashFields').style.display = 'block';
            } else if(this.value === '003') {
                document.getElementById('walletFields').style.display = 'block';
            }
        });
        
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const paymentType = document.getElementById('paymentType').value;
            
            if (paymentType === '003') {
                const accountNumber = document.getElementById('accountNumber').value;
                const referenceNo = document.getElementById('referenceNo').value;
                const walletType = document.getElementById('walletType').value;
                
                if (!walletType) {
                    alert('Please select a wallet type');
                    e.preventDefault();
                    return false;
                }
                
                if (!/^\d{11}$/.test(accountNumber)) {
                    alert('Account number must be exactly 11 digits!');
                    e.preventDefault();
                    return false;
                }
                
                if (walletType === '001' && !/^\d{13}$/.test(referenceNo)) {
                    alert('GCash reference number must be exactly 13 digits!');
                    e.preventDefault();
                    return false;
                }
                if (walletType === '002' && !/^\d{12}$/.test(referenceNo)) {
                    alert('PayMaya reference number must be exactly 12 digits!');
                    e.preventDefault();
                    return false;
                }
            }
            
            if (paymentType === '001') {
                const amountReceived = document.querySelector('input[name="amount_received"]').value;
                const totalAmount = <?= $totalAmount ?>;
                
                if (!amountReceived || amountReceived < totalAmount) {
                    alert('Amount received must be at least ₱' + totalAmount.toFixed(2));
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>