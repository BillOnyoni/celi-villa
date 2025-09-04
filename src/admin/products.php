<?php
// src/admin/products.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products - Admin</title>
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
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Products Management</h3>
    <a class="btn btn-success" href="product-add.php"><i class="fas fa-plus"></i> Add Product</a>
  </div>
  
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody>
            <?php while ($p = $products->fetch_assoc()): ?>
            <tr>
              <td><img src="/public/assets/img/<?= htmlspecialchars($p['image']) ?>" style="height:60px;object-fit:cover" class="rounded"></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
              <td>KES <?= number_format($p['price'],2) ?></td>
              <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="product-edit.php?id=<?= $p['product_id'] ?>">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <a class="btn btn-sm btn-danger" href="product-delete.php?id=<?= $p['product_id'] ?>" onclick="return confirm('Delete product?')">
                  <i class="fas fa-trash"></i> Delete
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>