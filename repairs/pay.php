<?php
require_once '../includes/database.php';

$repair_id = $_GET['id'] ?? '';
$message = '';
$messageType = '';

if (!$repair_id) {
    header("Location: list.php?error=No repair selected");
    exit();
}

// Get repair details
$repair = $conn->query("
    SELECT rf.*, ri.item_name, CONCAT(c.first_name, ' ', c.last_name) as client_name
    FROM Repair_Fee rf
    JOIN Rental_Item ri ON rf.item_id = ri.item_id
    JOIN TransactionTbl t ON rf.transaction_id = t.transaction_id
    JOIN Client c ON t.client_id = c.client_id
    WHERE rf.repair_fee_id = '$repair_id'
")->fetch_assoc();

if (!$repair) {
    header("Location: list.php?error=Repair not found");
    exit();
}

$quantity = $repair['quantity'] ?? 1;
$totalAmount = $quantity * $repair['cost'];

// Get payment types
$paymentTypes = $conn->query("SELECT * FROM Payment_Type WHERE payment_type_id IN ('001', '003')");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $payment_id = 'P' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
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
    
    if ($payment_type_id == '001') {
        $amount_received = $_POST['amount_received'];
        if ($amount_received < $amount) {
            $message = "Amount received must be at least ₱" . number_format($amount, 2);
            $messageType = "danger";
            $error = true;
        }
    }
    
    if (!$error) {
        $sql = "INSERT INTO Payment (payment_id, transaction_id, payment_date, amount, payment_type_id) 
                VALUES (?, NULL, CURDATE(), ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $payment_id, $amount, $payment_type_id);
        
        if ($stmt->execute()) {
            if ($payment_type_id == '001') {
                $change_amount = $amount_received - $amount;
                $conn->query("INSERT INTO Cash (payment_id, amount_received, change_amount) VALUES ('$payment_id', $amount_received, $change_amount)");
            }
            elseif ($payment_type_id == '003') {
                $conn->query("INSERT INTO Wallet (payment_id, wallet_type_id, account_number, transaction_reference_no) VALUES ('$payment_id', '$wallet_type_id', '$account_number', '$reference_no')");
            }
            
            $conn->query("UPDATE Repair_Fee SET status = 'Paid' WHERE repair_fee_id = '$repair_id'");
            
            header("Location: list.php?success=Payment recorded! Amount: ₱" . number_format($amount, 2));
            exit();
        } else {
            $message = "Database Error: " . $conn->error;
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
    <title>Pay Repair Fee - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 12px 30px;
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
            padding: 12px 30px;
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
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .total-amount { font-size: 24px; font-weight: 700; color: #10b981; }
        .button-group { display: flex; flex-direction: column; gap: 10px; margin-top: 25px; }
        .mb-3-custom { margin-bottom: 20px; }
        .duration-badge {
            background: #eef2ff;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
            color: #667eea;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .calculation-detail {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .error-border {
            border-color: #ef4444 !important;
        }
        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h3><i class="fas fa-tools"></i> Pay Repair Fee</h3>
            
            <?php if($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Repair ID:</span>
                    <span class="info-value"><?= htmlspecialchars($repair_id) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Client:</span>
                    <span class="info-value"><?= htmlspecialchars($repair['client_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Item:</span>
                    <span class="info-value"><?= htmlspecialchars($repair['item_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Quantity:</span>
                    <span class="info-value"><?= $quantity ?> unit(s)</span>
                </div>
                <div class="info-row mt-2">
                    <span class="info-label"><strong>Amount Due:</strong></span>
                    <span class="total-amount">₱<?= number_format($totalAmount, 2) ?></span>
                </div>
                <div class="calculation-detail">
                    <i class="fas fa-calculator"></i> Calculated as: <?= $quantity ?> unit(s) × ₱<?= number_format($repair['cost'], 2) ?> = ₱<?= number_format($totalAmount, 2) ?>
                </div>
            </div>
            
            <form method="POST" id="paymentForm" novalidate>
                <input type="hidden" name="submit_payment" value="1">
                
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
                        <input type="number" name="amount_received" id="amountReceived" class="form-control" step="0.01">
                        <small class="text-muted">Enter amount received from customer</small>
                        <div id="changeDisplay" style="margin-top: 8px; font-size: 13px; font-weight: 500;"></div>
                        <span class="error-message" id="amountReceivedError"></span>
                    </div>
                </div>
                
                <!-- Digital Wallet Fields -->
                <div id="walletFields" style="display:none;">
                    <div class="mb-3-custom">
                        <label class="form-label">WALLET TYPE</label>
                        <select name="wallet_type_id" id="walletType" class="form-select">
                            <option value="">Select Wallet</option>
                            <option value="001">GCash</option>
                            <option value="002">PayMaya</option>
                        </select>
                        <span class="error-message" id="walletTypeError"></span>
                    </div>
                    <div class="mb-3-custom">
                        <label class="form-label">ACCOUNT NUMBER</label>
                        <input type="text" name="account_number" id="accountNumber" class="form-control" placeholder="Enter 11-digit account number" maxlength="11">
                        <small class="text-muted">Must be exactly 11 digits</small>
                        <span class="error-message" id="accountNumberError"></span>
                    </div>
                    <div class="mb-3-custom">
                        <label class="form-label">REFERENCE NUMBER</label>
                        <input type="text" name="reference_no" id="referenceNo" class="form-control" placeholder="Enter reference number">
                        <small class="text-muted" id="refHint">GCash: 13 digits | PayMaya: 12 digits</small>
                        <span class="error-message" id="referenceNoError"></span>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" onclick="validateAndSubmit()" class="btn-payment">
                        <i class="fas fa-check-circle"></i> Record Payment (₱<?= number_format($totalAmount, 2) ?>)
                    </button>
                    <a href="list.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Repairs
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const totalAmount = <?= $totalAmount ?>;
        
        function validateAndSubmit() {
            // Clear all previous errors
            clearErrors();
            
            let isValid = true;
            const paymentType = document.getElementById('paymentType').value;
            
            if (!paymentType) {
                showError('paymentType', 'Please select a payment method');
                isValid = false;
            }
            
            if (paymentType === '001') {
                const amountReceived = document.getElementById('amountReceived').value;
                
                if (!amountReceived || parseFloat(amountReceived) < totalAmount) {
                    showError('amountReceived', `Amount received must be at least ₱${totalAmount.toFixed(2)}`);
                    isValid = false;
                }
            } 
            else if (paymentType === '003') {
                const walletType = document.getElementById('walletType').value;
                
                if (!walletType) {
                    showError('walletType', 'Please select a wallet type');
                    isValid = false;
                }
                
                const accountNumber = document.getElementById('accountNumber').value;
                if (!accountNumber || !/^\d{11}$/.test(accountNumber)) {
                    showError('accountNumber', 'Account number must be exactly 11 digits');
                    isValid = false;
                }
                
                const referenceNo = document.getElementById('referenceNo').value;
                if (walletType === '001') {
                    if (!referenceNo || !/^\d{13}$/.test(referenceNo)) {
                        showError('referenceNo', 'GCash reference number must be exactly 13 digits');
                        isValid = false;
                    }
                } else if (walletType === '002') {
                    if (!referenceNo || !/^\d{12}$/.test(referenceNo)) {
                        showError('referenceNo', 'PayMaya reference number must be exactly 12 digits');
                        isValid = false;
                    }
                }
            }
            
            if (isValid) {
                document.getElementById('paymentForm').submit();
            } else {
                const firstError = document.querySelector('.error-border');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.add('error-border');
                const errorSpan = document.getElementById(fieldId + 'Error');
                if (errorSpan) {
                    errorSpan.textContent = message;
                }
            }
        }
        
        function clearErrors() {
            const errorFields = document.querySelectorAll('.error-border');
            errorFields.forEach(field => {
                field.classList.remove('error-border');
            });
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                msg.textContent = '';
            });
        }
        
        document.getElementById('paymentType').addEventListener('change', function() {
            document.getElementById('cashFields').style.display = 'none';
            document.getElementById('walletFields').style.display = 'none';
            clearErrors();
            
            if(this.value === '001') {
                document.getElementById('cashFields').style.display = 'block';
                document.getElementById('amountReceived').required = true;
            } else if(this.value === '003') {
                document.getElementById('walletFields').style.display = 'block';
            }
        });
        
        const amountReceivedInput = document.getElementById('amountReceived');
        if (amountReceivedInput) {
            amountReceivedInput.addEventListener('input', function() {
                const amountReceived = parseFloat(this.value);
                const changeDisplay = document.getElementById('changeDisplay');
                const errorSpan = document.getElementById('amountReceivedError');
                
                if (!isNaN(amountReceived) && amountReceived >= totalAmount) {
                    const change = amountReceived - totalAmount;
                    changeDisplay.innerHTML = `<i class="fas fa-calculator"></i> Change: ₱${change.toFixed(2)}`;
                    changeDisplay.style.color = '#10b981';
                    if (errorSpan) errorSpan.textContent = '';
                    this.classList.remove('error-border');
                } else if (!isNaN(amountReceived) && amountReceived < totalAmount) {
                    const remaining = totalAmount - amountReceived;
                    changeDisplay.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Remaining: ₱${remaining.toFixed(2)}`;
                    changeDisplay.style.color = '#ef4444';
                } else {
                    changeDisplay.innerHTML = '';
                }
            });
        }
        
        document.getElementById('walletType')?.addEventListener('change', function() {
            const refHint = document.getElementById('refHint');
            const refInput = document.getElementById('referenceNo');
            const errorSpan = document.getElementById('referenceNoError');
            
            if (errorSpan) errorSpan.textContent = '';
            if (refInput) refInput.classList.remove('error-border');
            
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
        
        const accountNumberInput = document.getElementById('accountNumber');
        if (accountNumberInput) {
            accountNumberInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 11);
                const errorSpan = document.getElementById('accountNumberError');
                if (errorSpan && this.value.length === 11) {
                    errorSpan.textContent = '';
                    this.classList.remove('error-border');
                }
            });
        }
        
        const referenceNoInput = document.getElementById('referenceNo');
        if (referenceNoInput) {
            referenceNoInput.addEventListener('input', function() {
                const walletType = document.getElementById('walletType')?.value;
                const errorSpan = document.getElementById('referenceNoError');
                
                if (walletType === '001') {
                    this.value = this.value.replace(/\D/g, '').slice(0, 13);
                    if (errorSpan && this.value.length === 13) {
                        errorSpan.textContent = '';
                        this.classList.remove('error-border');
                    }
                } else if (walletType === '002') {
                    this.value = this.value.replace(/\D/g, '').slice(0, 12);
                    if (errorSpan && this.value.length === 12) {
                        errorSpan.textContent = '';
                        this.classList.remove('error-border');
                    }
                }
            });
        }
        
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.classList.remove('error-border');
                const errorSpan = document.getElementById(this.id + 'Error');
                if (errorSpan) errorSpan.textContent = '';
            });
        });
    </script>
</body>
</html>