<?php
// public/order-details.php
require_once __DIR__ . '/../src/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

$order_id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<div class='alert alert-danger'>Order not found.</div>";
    require_once __DIR__ . '/../src/includes/footer.php';
    exit();
}

// Get order items
$itemsStmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$itemsStmt->bind_param("i", $order_id);
$itemsStmt->execute();
$items = $itemsStmt->get_result();

// Get payment info
$paymentStmt = $conn->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
$paymentStmt->bind_param("i", $order_id);
$paymentStmt->execute();
$payment = $paymentStmt->get_result()->fetch_assoc();
?>

<div class="row">
    <div class="col-md-8">
        <h2>Order #<?= $order['order_id'] ?></h2>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5>Order Details</h5>
                <?php
                $statusClass = match($order['status']) {
                    'paid' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    'cancelled' => 'secondary',
                    default => 'info'
                };
                ?>
                <span class="badge bg-<?= $statusClass ?> fs-6"><?= ucfirst($order['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        <p><strong>Total Amount:</strong> KES <?= formatPrice($order['total']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php if ($payment): ?>
                        <p><strong>Payment Method:</strong> M-Pesa</p>
                        <?php if ($payment['mpesa_receipt']): ?>
                        <p><strong>M-Pesa Receipt:</strong> <?= htmlspecialchars($payment['mpesa_receipt']) ?></p>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Items Ordered</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/public/assets/img/<?= htmlspecialchars($item['image']) ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover;" 
                                             class="me-3 rounded">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </div>
                                </td>
                                <td>KES <?= formatPrice($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>KES <?= formatPrice($item['price'] * $item['quantity']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="3" class="text-end">Total:</th>
                                <th>KES <?= formatPrice($order['total']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Order Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/public/account.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <?php if ($order['status'] === 'pending'): ?>
                    <button class="btn btn-warning" onclick="checkPaymentStatus(<?= $order_id ?>)">
                        <i class="fas fa-sync"></i> Check Payment Status
                    </button>
                    <?php endif; ?>
                    <a href="/public/products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
        
        <?php if ($order['status'] === 'paid'): ?>
        <div class="card mt-3">
            <div class="card-body text-center">
                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                <h6>Order Completed</h6>
                <p class="small text-muted">Thank you for your purchase!</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function checkPaymentStatus(orderId) {
    fetch('/public/api/check-payment-status.php?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'paid') {
                alert('Payment successful!');
                location.reload();
            } else if (data.status === 'failed') {
                alert('Payment failed.');
            } else {
                alert('Payment still pending.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error checking payment status');
        });
}
</script>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>