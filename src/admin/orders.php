<?php
// src/admin/orders.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$viewOrder = (int)($_GET['view'] ?? 0);
if ($viewOrder) {
    // Show order details
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email, u.phone, u.address FROM orders o JOIN users u ON o.user_id=u.user_id WHERE o.order_id = ? LIMIT 1");
    $stmt->bind_param("i", $viewOrder);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if ($order) {
        $itemsStmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        $itemsStmt->bind_param("i", $viewOrder);
        $itemsStmt->execute();
        $items = $itemsStmt->get_result();
    }
}

$orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY o.created_at DESC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Orders - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Celica Admin</a>
    <div>
      <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
      <a class="btn btn-outline-light btn-sm" href="/public/logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <?php if ($viewOrder && $order): ?>
  <!-- Order Details View -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Order #<?= $order['order_id'] ?> Details</h3>
    <div>
      <a href="print-receipt.php?order=<?= $order['order_id'] ?>" class="btn btn-secondary" target="_blank">
        <i class="fas fa-print"></i> Print Receipt
      </a>
      <a href="orders.php" class="btn btn-primary">Back to Orders</a>
    </div>
  </div>
  
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5>Customer Information</h5>
        </div>
        <div class="card-body">
          <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
          <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
          <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5>Order Information</h5>
        </div>
        <div class="card-body">
          <p><strong>Order Date:</strong> <?= $order['created_at'] ?></p>
          <p><strong>Status:</strong> 
            <?php
            $statusClass = match($order['status']) {
                'paid' => 'success',
                'pending' => 'warning',
                'failed' => 'danger',
                default => 'info'
            };
            ?>
            <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($order['status']) ?></span>
          </p>
          <p><strong>Total:</strong> KES <?= formatPrice($order['total']) ?></p>
        </div>
      </div>
    </div>
  </div>
  
  <div class="card mt-3">
    <div class="card-header">
      <h5>Order Items</h5>
    </div>
    <div class="card-body">
      <table class="table">
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php while($item = $items->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>KES <?= formatPrice($item['price']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>KES <?= formatPrice($item['price'] * $item['quantity']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr class="table-active">
            <th colspan="3" class="text-end">Total</th>
            <th>KES <?= formatPrice($order['total']) ?></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  
  <?php else: ?>
  <!-- Orders List View -->
  <h3>Orders Management</h3>
  
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
          <tbody>
            <?php while($order = $orders->fetch_assoc()): ?>
            <tr>
              <td><?= $order['order_id'] ?></td>
              <td><?= htmlspecialchars($order['username']) ?></td>
              <td>KES <?= formatPrice($order['total']) ?></td>
              <td>
                <?php
                $statusClass = match($order['status']) {
                    'paid' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    default => 'info'
                };
                ?>
                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($order['status']) ?></span>
              </td>
              <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="?view=<?= $order['order_id'] ?>">
                  <i class="fas fa-eye"></i> View
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>