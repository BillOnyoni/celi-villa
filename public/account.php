<?php
// public/account.php
require_once __DIR__ . '/../src/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<h2>My Account</h2>

<div class="row">
    <div class="col-md-8">
        <h4>My Orders</h4>
        <?php if ($orders->num_rows === 0): ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="/public/products.php">Start shopping</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $order['order_id'] ?></td>
                        <td>KES <?= formatPrice($order['total']) ?></td>
                        <td>
                            <?php
                            $statusClass = match($order['status']) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'cancelled' => 'secondary',
                                default => 'info'
                            };
                            ?>
                            <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($order['status']) ?></span>
                        </td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="/public/order-details.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Account Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/public/settings.php" class="btn btn-outline-primary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="/public/products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                    <a href="/public/logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>