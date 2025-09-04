<?php
// src/admin/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

// simple stats
$q1 = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$q2 = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$q3 = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$q4 = $conn->query("SELECT SUM(amount_paid) as total FROM payments WHERE status='completed'")->fetch_assoc()['total'] ?? 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard - Celica Computers Villa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#"><i class="fas fa-tachometer-alt"></i> Celica Admin</a>
    <div>
      <a class="btn btn-outline-light btn-sm" href="products.php">Products</a>
      <a class="btn btn-outline-light btn-sm" href="users.php">Users</a>
      <a class="btn btn-outline-light btn-sm" href="orders.php">Orders</a>
      <a class="btn btn-outline-light btn-sm" href="payments.php">Payments</a>
      <a class="btn btn-outline-light btn-sm" href="mpesa-logs.php">M-Pesa Logs</a>
      <a class="btn btn-outline-light btn-sm" href="/public/index.php">View Site</a>
      <a class="btn btn-outline-light btn-sm" href="/public/logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3>Dashboard Overview</h3>
  
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5>Users</h5>
              <h3><?= $q1 ?></h3>
            </div>
            <i class="fas fa-users fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5>Products</h5>
              <h3><?= $q2 ?></h3>
            </div>
            <i class="fas fa-box fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5>Pending Orders</h5>
              <h3><?= $q3 ?></h3>
            </div>
            <i class="fas fa-clock fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5>Revenue (KES)</h5>
              <h3><?= number_format($q4,0) ?></h3>
            </div>
            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5>Recent Orders</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm">
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
                  <td>
                    <?php
                    $statusClass = match($row['status']) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'info'
                    };
                    ?>
                    <span class="badge bg-<?= $statusClass ?>"><?= $row['status'] ?></span>
                  </td>
                  <td><?= date('M j', strtotime($row['created_at'])) ?></td>
                  <td><a class="btn btn-sm btn-outline-primary" href="orders.php?view=<?= $row['order_id'] ?>">View</a></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h6>Quick Actions</h6>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="product-add.php" class="btn btn-success">
              <i class="fas fa-plus"></i> Add Product
            </a>
            <a href="orders.php" class="btn btn-primary">
              <i class="fas fa-list"></i> View All Orders
            </a>
            <a href="mpesa-logs.php" class="btn btn-info">
              <i class="fas fa-file-alt"></i> M-Pesa Logs
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>