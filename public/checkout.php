<?php
// public/checkout.php
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/config/mpesa.php';

// Load environment variables
if (file_exists(__DIR__ . '/../src/config/env.php')) {
    require_once __DIR__ . '/../src/config/env.php';
}

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo "<div class='alert alert-info'>Cart is empty. <a href='/public/products.php'>Shop</a></div>";
    require_once __DIR__ . '/../src/includes/footer.php';
    exit();
}

// Get user details
$userStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// compute total and insert order & items
$ids = implode(',', array_map('intval', array_keys($cart)));
$sql = "SELECT * FROM products WHERE product_id IN ($ids)";
$res = $conn->query($sql);
$total = 0.0;
$cartItems = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['product_id'];
    $qty = $cart[$pid];
    $subtotal = $row['price'] * $qty;
    $total += $subtotal;
    $cartItems[] = [
        'product' => $row,
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

if (isset($_POST['pay'])) {
    $phone = sanitize($_POST['phone']);
    
    try {
        // Create order (pending)
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?,?, 'pending')");
        $stmt->bind_param("id", $_SESSION['user_id'], $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order items
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

        $mpesa = new MpesaAPI();
        $callbackUrl = ($_ENV['SITE_URL'] ?? 'https://yourdomain.com') . '/public/api/mpesa-callback.php';
        
        $response = $mpesa->stkPush(
            $phone,
            (int)round($total),
            "Order-$order_id",
            "Payment for order $order_id - Celica Computers Villa",
            $callbackUrl
        );
        
        // Store the STK request in payments table
        $checkoutRequestId = $response['CheckoutRequestID'];
        $merchantRequestId = $response['MerchantRequestID'];
        
        $pstmt = $conn->prepare("INSERT INTO payments (user_id, order_id, amount, mpesa_code, merchant_request_id, status) VALUES (?,?,?,?,?,'pending')");
        $pstmt->bind_param("iidss", $_SESSION['user_id'], $order_id, $total, $checkoutRequestId, $merchantRequestId);
        $pstmt->execute();
        $pstmt->close();
        
        $success = "Payment request sent to your phone. Please enter your M-Pesa PIN to complete the transaction.";
        
        // Store order ID in session for status checking
        $_SESSION['pending_order'] = $order_id;
        
    } catch (Exception $e) {
        $error = "Payment failed: " . $e->getMessage();
        
        // Update order status to failed if order was created
        if (isset($order_id)) {
            $upd = $conn->prepare("UPDATE orders SET status = 'failed' WHERE order_id = ?");
            $upd->bind_param("i", $order_id);
            $upd->execute();
        }
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <h2>Checkout</h2>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <?php foreach ($cartItems as $item): ?>
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong><?= htmlspecialchars($item['product']['name']) ?></strong>
                        <br><small class="text-muted">Qty: <?= $item['qty'] ?> × KES <?= formatPrice($item['product']['price']) ?></small>
                    </div>
                    <span>KES <?= formatPrice($item['subtotal']) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <h5>Total:</h5>
                    <h5 class="text-primary">KES <?= formatPrice($total) ?></h5>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Payment Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number (for M-Pesa)</label>
                        <input name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                               class="form-control" placeholder="0712345678" required>
                        <small class="form-text text-muted">Enter your Safaricom number (format: 0712345678)</small>
                    </div>
                    <div class="col-12">
                        <button name="pay" class="btn btn-success btn-lg">
                            <i class="fas fa-mobile-alt"></i> Pay KES <?= formatPrice($total) ?> with M-Pesa
                        </button>
                        <a href="/public/cart.php" class="btn btn-secondary">Back to cart</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>How M-Pesa Payment Works</h6>
            </div>
            <div class="card-body">
                <ol class="small">
                    <li>Click "Pay with M-Pesa"</li>
                    <li>You'll receive an STK push on your phone</li>
                    <li>Enter your M-Pesa PIN</li>
                    <li>Payment confirmation will be instant</li>
                </ol>
                <div class="alert alert-info small">
                    <i class="fas fa-shield-alt"></i> Secure payment powered by Safaricom M-Pesa
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['pending_order'])): ?>
<div class="mt-4">
    <div class="alert alert-warning">
        <h5><i class="fas fa-clock"></i> Payment Status</h5>
        <p>Waiting for payment confirmation...</p>
        <button class="btn btn-sm btn-outline-primary" onclick="checkPaymentStatus(<?= $_SESSION['pending_order'] ?>)">Check Status</button>
        <div id="payment-status" class="mt-2"></div>
    </div>
</div>

<script>
function checkPaymentStatus(orderId) {
    const statusDiv = document.getElementById('payment-status');
    statusDiv.innerHTML = '<small class="text-muted">Checking...</small>';
    
    fetch('/public/api/check-payment-status.php?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'paid') {
                statusDiv.innerHTML = '<div class="alert alert-success small">Payment successful! Redirecting...</div>';
                setTimeout(() => {
                    window.location.href = '/public/account.php';
                }, 2000);
            } else if (data.status === 'failed') {
                statusDiv.innerHTML = '<div class="alert alert-danger small">Payment failed. Please try again.</div>';
            } else if (data.status === 'cancelled') {
                statusDiv.innerHTML = '<div class="alert alert-warning small">Payment was cancelled.</div>';
            } else {
                statusDiv.innerHTML = '<small class="text-muted">Payment still pending...</small>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusDiv.innerHTML = '<small class="text-danger">Error checking status</small>';
        });
}

// Auto-check payment status every 5 seconds
<?php if (isset($_SESSION['pending_order'])): ?>
const statusInterval = setInterval(() => {
    checkPaymentStatus(<?= $_SESSION['pending_order'] ?>);
}, 5000);

// Stop checking after 5 minutes
setTimeout(() => {
    clearInterval(statusInterval);
}, 300000);
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>