<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

// simple stats
$q1 = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$q2 = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$q3 = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$q4 = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'] ?? 0;
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin - Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Celica Admin</a>
    <div>
      <a class="btn btn-outline-light btn-sm" href="/admin/products.php">Products</a>
      <a class="btn btn-outline-light btn-sm" href="/admin/users.php">Users</a>
      <a class="btn btn-outline-light btn-sm" href="/admin/orders.php">Orders</a>
      <a class="btn btn-outline-light btn-sm" href="/admin/payments.php">Payments</a>
      <a class="btn btn-outline-light btn-sm" href="/logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="container mt-4">
  <div class="row g-3">
    <div class="col-md-3"><div class="card p-3"><h5>Users</h5><h3><?= $q1 ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3"><h5>Products</h5><h3><?= $q2 ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3"><h5>Pending Orders</h5><h3><?= $q3 ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3"><h5>Total Collected (KES)</h5><h3><?= number_format($q4,2) ?></h3></div></div>
  </div>

  <div class="mt-4">
    <h4>Recent Orders</h4>
    <table class="table">
      <thead><tr><th>#</th><th>User</th><th>Total</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
      <tbody>
        <?php
        $res = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY o.created_at DESC LIMIT 10");
        while ($row = $res->fetch_assoc()):
        ?>
        <tr>
          <td><?= $row['order_id'] ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= number_format($row['total'],2) ?></td>
          <td><?= $row['status'] ?></td>
          <td><?= $row['created_at'] ?></td>
          <td><a class="btn btn-sm btn-primary" href="orders.php?view=<?= $row['order_id'] ?>">View</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
