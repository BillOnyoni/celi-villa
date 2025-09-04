<?php
// admin/product_add.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = $_POST['description']; // allow html? we keep plain text
    $price = (float)$_POST['price'];
    $category = !empty($_POST['category']) ? (int)$_POST['category'] : null;
    try {
        $imageName = handleImageUpload($_FILES['image'] ?? null, __DIR__ . '/../assets/img');
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
<html><head><meta charset="utf-8"><title>Add Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4">
  <h3>Add Product</h3>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
  <form method="POST" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6"><label>Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-6"><label>Price (KES)</label><input name="price" type="number" step="0.01" class="form-control" required></div>
    <div class="col-md-6"><label>Category</label>
      <select name="category" class="form-control">
        <option value="">— None —</option>
        <?php while($c = $categories->fetch_assoc()): ?>
          <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-12"><label>Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
    <div class="col-md-6"><label>Image</label><input name="image" type="file" accept="image/*" class="form-control"></div>
    <div class="col-12"><button class="btn btn-success">Add Product</button> <a href="products.php" class="btn btn-secondary">Back</a></div>
  </form>
</div>
</body>
</html>
