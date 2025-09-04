<?php
// admin/products.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Products - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Products</h3>
    <a class="btn btn-success" href="product_add.php">+ Add Product</a>
  </div>
  <table class="table table-striped">
    <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Created</th><th></th></tr></thead>
    <tbody>
      <?php while ($p = $products->fetch_assoc()): ?>
      <tr>
        <td><img src="/assets/img/<?= htmlspecialchars($p['image']) ?>" style="height:60px;object-fit:cover"></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
        <td><?= number_format($p['price'],2) ?></td>
        <td><?= $p['created_at'] ?></td>
        <td>
          <a class="btn btn-sm btn-primary" href="product_edit.php?id=<?= $p['product_id'] ?>">Edit</a>
          <a class="btn btn-sm btn-danger" href="product_delete.php?id=<?= $p['product_id'] ?>" onclick="return confirm('Delete product?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
