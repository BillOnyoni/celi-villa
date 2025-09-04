<?php
// checkout.php
require_once 'includes/header.php';
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

// --- M-Pesa STK push ---
$consumerKey = 'YOUR_CONSUMER_KEY';
$consumerSecret = 'YOUR_CONSUMER_SECRET';
$BusinessShortCode = '174379'; // sandbox example
$Passkey = 'YOUR_PASSKEY';
$Timestamp = date("YmdHis");
$Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

// Amount and phone (collected from logged in user or ask here)
$amount = (int)round($total);
$phone = ''; // ask the user for phone if not in profile.

if (isset($_POST['pay'])) {
    $phone = preg_replace('/\D/', '', $_POST['phone']); // digits only
    if (substr($phone, 0, 1) === '0') $phone = '254' . substr($phone, 1);
    // Get access token
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic '.base64_encode($consumerKey.':'.$consumerSecret)]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($curl);
    curl_close($curl);
    $tokenJSON = json_decode($response, true);
    $access_token = $tokenJSON['access_token'] ?? null;
    if (!$access_token) {
        $error = "Could not get access token. Check credentials and internet.";
    } else {
        $curl2 = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
        $payload = [
            "BusinessShortCode" => $BusinessShortCode,
            "Password" => $Password,
            "Timestamp" => $Timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $BusinessShortCode,
            "PhoneNumber" => $phone,
            "CallBackURL" => "https://your-public-domain/callback.php",
            "AccountReference" => "Order-$order_id",
            "TransactionDesc" => "Payment for order $order_id"
        ];
        curl_setopt($curl2, CURLOPT_HTTPHEADER, ['Content-Type:application/json','Authorization:Bearer '.$access_token]);
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl2, CURLOPT_POST, TRUE);
        curl_setopt($curl2, CURLOPT_POSTFIELDS, json_encode($payload));
        $response2 = curl_exec($curl2);
        curl_close($curl2);
        $resObj = json_decode($response2, true);
        // store the STK request in payments table if available
        $mpesa_code = $resObj['CheckoutRequestID'] ?? null;
        $pstmt = $conn->prepare("INSERT INTO payments (user_id, order_id, amount, mpesa_code) VALUES (?,?,?,?)");
        $pstmt->bind_param("iids", $_SESSION['user_id'], $order_id, $amount, $mpesa_code);
        $pstmt->execute();
        $pstmt->close();

        $success = "STK request sent. Please approve payment on your phone.";
        // empty cart only after mpesa callback marks order paid
        unset($_SESSION['cart']);
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
    <input name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? '07XXXXXXXX'); ?>" class="form-control" required>
  </div>
  <div class="col-12">
    <button name="pay" class="btn btn-primary">Pay with M-Pesa (STK Push)</button>
    <a href="/cart.php" class="btn btn-secondary">Back to cart</a>
  </div>
</form>

<?php require_once 'includes/footer.php'; ?>
