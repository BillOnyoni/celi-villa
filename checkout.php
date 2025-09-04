<?php
// checkout.php
require_once 'includes/header.php';
require_once 'config/mpesa.php';

// Load environment variables
if (file_exists(__DIR__ . '/config/env.php')) {
    require_once 'config/env.php';
}

if (!isset($_SESSION['user_id'])) {
    redirect('/login.php');
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo "<div class='alert alert-info'>Cart is empty. <a href='/products.php'>Shop</a></div>";
    require_once 'includes/footer.php';
    exit();
}

// compute total and insert order & items
$ids = implode(',', array_map('intval', array_keys($cart)));
$sql = "SELECT * FROM products WHERE product_id IN ($ids)";
$res = $conn->query($sql);
$total = 0.0;
while ($row = $res->fetch_assoc()) {
    $pid = $row['product_id'];
    $qty = $cart[$pid];
    $total += $row['price'] * $qty;
}

// Create order (pending)
$stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?,?, 'pending')");
$stmt->bind_param("id", $_SESSION['user_id'], $total);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// insert order items
$stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
foreach ($cart as $pid => $qty) {
    // get product price
    $pstmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
    $pstmt->bind_param("i", $pid);
    $pstmt->execute();
    $pres = $pstmt->get_result()->fetch_assoc();
    $price = $pres['price'];
    $stmtItem->bind_param("iiid", $order_id, $pid, $qty, $price);
    $stmtItem->execute();
    $pstmt->close();
}
$stmtItem->close();

$amount = (int)round($total);

if (isset($_POST['pay'])) {
    $phone = sanitize($_POST['phone']);
    
    try {
        $mpesa = new MpesaAPI();
        $callbackUrl = ($_ENV['SITE_URL'] ?? 'https://yourdomain.com') . '/callback.php';
        
        $response = $mpesa->stkPush(
            $phone,
            $amount,
            "Order-$order_id",
            "Payment for order $order_id",
            $callbackUrl
        );
        
        // Store the STK request in payments table
        $checkoutRequestId = $response['CheckoutRequestID'];
        $merchantRequestId = $response['MerchantRequestID'];
        
        $pstmt = $conn->prepare("INSERT INTO payments (user_id, order_id, amount, mpesa_code, merchant_request_id, status) VALUES (?,?,?,?,?,'pending')");
        $pstmt->bind_param("iidss", $_SESSION['user_id'], $order_id, $amount, $checkoutRequestId, $merchantRequestId);
        $pstmt->execute();
        $pstmt->close();
        
        $success = "Payment request sent to your phone. Please enter your M-Pesa PIN to complete the transaction.";
        
        // Store order ID in session for status checking
        $_SESSION['pending_order'] = $order_id;
        
    } catch (Exception $e) {
        $error = "Payment failed: " . $e->getMessage();
        
        // Update order status to failed
        $upd = $conn->prepare("UPDATE orders SET status = 'failed' WHERE order_id = ?");
        $upd->bind_param("i", $order_id);
        $upd->execute();
    }
}
?>

<h2>Checkout</h2>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<p>Total: <strong>KES <?php echo formatPrice($total); ?></strong></p>

<form method="POST" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Phone (for M-Pesa)</label>
    <input name="phone" value="<?php echo htmlspecialchars($order['phone'] ?? '0712345678'); ?>" class="form-control" placeholder="0712345678" required>
    <small class="form-text text-muted">Enter your Safaricom number (format: 0712345678)</small>
  </div>
  <div class="col-12">
    <button name="pay" class="btn btn-primary">Pay with M-Pesa (STK Push)</button>
    <a href="/cart.php" class="btn btn-secondary">Back to cart</a>
  </div>
</form>

<?php if (isset($_SESSION['pending_order'])): ?>
<div class="mt-4">
    <div class="alert alert-info">
        <h5>Payment Status</h5>
        <p>Waiting for payment confirmation...</p>
        <button class="btn btn-sm btn-outline-primary" onclick="checkPaymentStatus(<?= $_SESSION['pending_order'] ?>)">Check Status</button>
    </div>
</div>

<script>
function checkPaymentStatus(orderId) {
    fetch('/check_payment_status.php?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'paid') {
                alert('Payment successful! Redirecting to your orders...');
                window.location.href = '/account.php';
            } else if (data.status === 'failed') {
                alert('Payment failed. Please try again.');
                location.reload();
            } else {
                alert('Payment still pending. Please complete the transaction on your phone.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error checking payment status');
        });
}

// Auto-check payment status every 10 seconds
<?php if (isset($_SESSION['pending_order'])): ?>
setInterval(() => checkPaymentStatus(<?= $_SESSION['pending_order'] ?>), 10000);
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
