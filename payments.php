<?php
// admin/payments.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$q = sanitize($_GET['q'] ?? '');
$sql = "SELECT p.*, u.username, o.order_id FROM payments p LEFT JOIN users u ON p.user_id=u.user_id LEFT JOIN orders o ON p.order_id=o.order_id WHERE 1";
if ($q !== '') {
    $sql .= " AND (p.mpesa_code LIKE '%" . $conn->real_escape_string($q) . "%' OR u.username LIKE '%" . $conn->real_escape_string($q) . "%')";
}
$sql .= " ORDER BY p.created_at DESC";
$res = $conn->query($sql);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Payments</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4">
  <h3>Payments</h3>
  <form class="mb-3"><div class="input-group">
    <input name="q" class="form-control" placeholder="Search by mpesa code or user" value="<?= htmlspecialchars($q) ?>">
    <button class="btn btn-outline-secondary">Search</button>
  </div></form>

  <table class="table">
    <thead><tr><th>#</th><th>User</th><th>Order</th><th>Amount</th><th>Mpesa Ref</th><th>Created</th></tr></thead>
    <tbody>
      <?php while($p = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $p['payment_id'] ?></td>
        <td><?= htmlspecialchars($p['username']) ?></td>
        <td><?= $p['order_id'] ?></td>
        <td><?= number_format($p['amount'],2) ?></td>
        <td><?= htmlspecialchars($p['mpesa_code']) ?></td>
        <td><?= $p['created_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body></html>
