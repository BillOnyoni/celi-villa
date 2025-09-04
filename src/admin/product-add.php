<?php
// src/admin/product-add.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $category = !empty($_POST['category']) ? (int)$_POST['category'] : null;
    
    try {
        $imageName = handleImageUpload($_FILES['image'] ?? null, __DIR__ . '/../../public/assets/img');
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category_id) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssdsi", $name, $description, $price, $imageName, $category);
        if ($stmt->execute()) {
            redirect('products.php');
        } else {
            $error = "DB error: " . $conn->error;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Product - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Celica Admin</a>
    <div>
      <a class="btn btn-outline-light btn-sm" href="products.php">Products</a>
      <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3>Add New Product</h3>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
  
  <div class="card">
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Product Name</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Price (KES)</label>
          <input name="price" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select name="category" class="form-control">
            <option value="">— Select Category —</option>
            <?php while($c = $categories->fetch_assoc()): ?>
              <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Product Image</label>
          <input name="image" type="file" accept="image/*" class="form-control">
          <small class="form-text text-muted">Max 5MB. Supported: JPG, PNG, WebP, GIF</small>
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Enter product description..."></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-success">
            <i class="fas fa-plus"></i> Add Product
          </button>
          <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>