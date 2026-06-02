<?php
require_once '../includes/database.php';

$transaction_id = $_GET['transaction_id'] ?? '';
$message = '';

// Get payment types
$paymentTypes = $conn->query("SELECT * FROM Payment_Type");
$cardTypes = $conn->query("SELECT * FROM Card_Type");
$walletTypes = $conn->query("SELECT * FROM Wallet_Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = generateId('P', 'Payment', 'payment_id');
    $payment_type_id = $_POST['payment_type_id'];
    $amount = $_POST['amount'];
    
    // Insert into Payment table
    $sql = "INSERT INTO Payment (payment_id, transaction_id, payment_date, amount, payment_type_id) 
            VALUES (?, ?, CURDATE(), ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $payment_id, $transaction_id, $amount, $payment_type_id);
    
    if ($stmt->execute()) {
        // Handle specific payment method
        if ($payment_type_id == '001') { // Cash
            $amount_received = $_POST['amount_received'];
            $change_amount = $amount_received - $amount;
            $conn->query("INSERT INTO Cash VALUES ('$payment_id', $amount_received, $change_amount)");
            $message = "Cash payment recorded! Change: ₱$change_amount";
        } 
        elseif ($payment_type_id == '002') { // Card
            $card_type_id = $_POST['card_type_id'];
            $approval_code = $_POST['approval_code'];
            $conn->query("INSERT INTO Card VALUES ('$payment_id', '$card_type_id', '$approval_code')");
            $message = "Card payment recorded! Approval: $approval_code";
        }
        elseif ($payment_type_id == '003') { // Digital Wallet
            $wallet_type_id = $_POST['wallet_type_id'];
            $account_number = $_POST['account_number'];
            $reference_no = $_POST['reference_no'];
            $conn->query("INSERT INTO Digital_Wallet VALUES ('$payment_id', '$wallet_type_id', '$account_number', '$reference_no')");
            $message = "Digital wallet payment recorded! Ref: $reference_no";
        }
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Process Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
body {
    background: #f3f4f6;
    font-family: 'Poppins', sans-serif;
}

.payment-container {
    max-width: 700px;
    margin: 50px auto;
}

.payment-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}

.payment-header {
    background: white;
    padding: 25px 30px 10px;
    border-bottom: 2px solid #eef2ff;
}

.payment-header h3 {
    font-weight: 700;
    margin: 0;
}

.payment-header i {
    color: #6366f1;
    margin-right: 8px;
}

.payment-body {
    padding: 30px;
}

.form-label {
    font-weight: 600;
    color: #374151;
}

.form-control,
.form-select {
    border-radius: 10px;
    padding: 12px;
    border: 1px solid #d1d5db;
}

.form-control:focus,
.form-select:focus {
    box-shadow: none;
    border-color: #6366f1;
}

.transaction-box {
    background: #f9fafb;
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
}

.btn-payment {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
    width: 100%;
    transition: .3s;
}

.btn-payment:hover {
    background: #059669;
    color: white;
}

.btn-back {
    background: #6b7280;
    color: white;
    border-radius: 12px;
    padding: 12px;
    text-decoration: none;
    display: block;
    text-align: center;
    font-weight: 600;
    transition: .3s;
}

.btn-back:hover {
    background: #4b5563;
    color: white;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #111827;
}
</style>
</head>
<body>

<div class="payment-container">
    <div class="payment-card">

        <div class="payment-header">
            <h3>
                <i class="fas fa-credit-card"></i>
                Process Payment
            </h3>
        </div>

        <div class="payment-body">

            <?php if($message): ?>
                <div class="alert alert-info">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <!-- Transaction ID -->
                <div class="mb-4">
                    <label class="form-label">Transaction ID</label>

                    <div class="transaction-box">
                        <?= htmlspecialchars($transaction_id) ?>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>

                    <select name="payment_type_id"
                            id="paymentType"
                            class="form-select"
                            required>

                        <option value="">Select Payment Method</option>

                        <?php while($pt = $paymentTypes->fetch_assoc()): ?>
                            <option value="<?= $pt['payment_type_id'] ?>">
                                <?= $pt['type_name'] ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <!-- Amount -->
                <div class="mb-3">
                    <label class="form-label">Amount (₱)</label>

                    <input type="number"
                           name="amount"
                           class="form-control"
                           required>
                </div>

                <!-- Cash Fields -->
                <div id="cashFields" style="display:none;">

                    <div class="mb-3">
                        <label class="form-label">
                            Amount Received
                        </label>

                        <input type="number"
                               name="amount_received"
                               class="form-control">
                    </div>

                </div>

                <!-- Card Fields -->
                <div id="cardFields" style="display:none;">

                    <div class="mb-3">
                        <label class="form-label">
                            Card Type
                        </label>

                        <select name="card_type_id"
                                class="form-select">

                            <?php while($ct = $cardTypes->fetch_assoc()): ?>
                                <option value="<?= $ct['card_type_id'] ?>">
                                    <?= $ct['type_name'] ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Approval Code
                        </label>

                        <input type="text"
                               name="approval_code"
                               class="form-control">
                    </div>

                </div>

                <!-- Wallet Fields -->
                <div id="walletFields" style="display:none;">

                    <div class="mb-3">
                        <label class="form-label">
                            Wallet Type
                        </label>

                        <select name="wallet_type_id"
                                class="form-select">

                            <?php while($wt = $walletTypes->fetch_assoc()): ?>
                                <option value="<?= $wt['wallet_type_id'] ?>">
                                    <?= $wt['type_name'] ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Account Number
                        </label>

                        <input type="text"
                               name="account_number"
                               class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Reference Number
                        </label>

                        <input type="text"
                               name="reference_no"
                               class="form-control">
                    </div>

                </div>

                <!-- Buttons -->
                <div class="d-grid gap-2 mt-4">

                    <button type="submit" class="btn-payment">
                        <i class="fas fa-check-circle"></i>
                        Process Payment
                    </button>

                    <a href="../transactions/view.php?id=<?= $transaction_id ?>"
                       class="btn-back">

                        <i class="fas fa-arrow-left"></i>
                        Back to Transaction

                    </a>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.getElementById('paymentType').addEventListener('change', function() {

    document.getElementById('cashFields').style.display = 'none';
    document.getElementById('cardFields').style.display = 'none';
    document.getElementById('walletFields').style.display = 'none';

    if(this.value === '001') {
        document.getElementById('cashFields').style.display = 'block';
    }
    else if(this.value === '002') {
        document.getElementById('cardFields').style.display = 'block';
    }
    else if(this.value === '003') {
        document.getElementById('walletFields').style.display = 'block';
    }
});
</script>

</body>
</html>