<?php
// admin/print_receipt.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$orderId = (int)($_GET['order'] ?? 0);
if (!$orderId) exit('Order ID required.');

$stmt = $conn->prepare("SELECT o.*, u.username, u.email, u.phone, u.address FROM orders o JOIN users u ON o.user_id=u.user_id WHERE o.order_id = ? LIMIT 1");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) exit('Order not found.');

$items = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$items->bind_param("i", $orderId);
$items->execute();
$itemsRes = $items->get_result();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Receipt #<?= $orderId ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@media print { .no-print { display:none } }
</style>
</head>
<body>
<div class="container mt-3">
  <div class="d-flex justify-content-between">
    <div><h4>Celica Computers Villa</h4><small>Moi Avenue, Nairobi (next to MKU Towers)</small></div>
    <div class="text-end">
      <h5>Receipt #<?= $orderId ?></h5>
      <small><?= $order['created_at'] ?></small>
    </div>
  </div>

  <hr>
  <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?><br>
     <strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?><br>
     <strong>Address:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>

  <table class="table">
    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php while($it = $itemsRes->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($it['name']) ?></td>
        <td><?= number_format($it['price'],2) ?></td>
        <td><?= $it['quantity'] ?></td>
        <td><?= number_format($it['price']*$it['quantity'],2) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
    <tfoot>
      <tr><th colspan="3" class="text-end">Total</th><th>KES <?= number_format($order['total'],2) ?></th></tr>
    </tfoot>
  </table>

  <div class="mt-4 no-print">
    <button class="btn btn-primary" onclick="window.print()">Print</button>
    <a class="btn btn-secondary" href="orders.php">Back</a>
  </div>
</div>
</body></html>
