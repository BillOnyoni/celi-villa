<?php
// admin/product_edit.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
stmt_execute_or_die($stmt);
$res = $stmt->get_result();
$product = $res->fetch_assoc();
if (!$product) redirect('products.php');

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $category = !empty($_POST['category']) ? (int)$_POST['category'] : null;

    try {
        // If a new image uploaded replace, else keep old
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageName = handleImageUpload($_FILES['image'], __DIR__ . '/../assets/img');
            // optionally delete old file if not placeholder
            if ($product['image'] && $product['image'] !== 'placeholder.png') {
                @unlink(__DIR__ . '/../assets/img/' . $product['image']);
            }
        } else {
            $imageName = $product['image'];
        }

        $upd = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=?, category_id=? WHERE product_id=?");
        $upd->bind_param("ssdsii", $name, $description, $price, $imageName, $category, $id);
        if ($upd->execute()) {
            redirect('products.php');
        } else {
            $error = "DB error: " . $conn->error;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
function stmt_execute_or_die($s) { if (!$s->execute()) die($s->error); }
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Edit Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4">
  <h3>Edit Product</h3>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6"><label>Name</label><input name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required></div>
    <div class="col-md-6"><label>Price (KES)</label><input name="price" type="number" step="0.01" value="<?= $product['price'] ?>" class="form-control" required></div>
    <div class="col-md-6"><label>Category</label>
      <select name="category" class="form-control">
        <option value="">— None —</option>
        <?php while($c = $categories->fetch_assoc()): ?>
          <option value="<?= $c['category_id'] ?>" <?= $product['category_id'] == $c['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-12"><label>Description</label><textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea></div>
    <div class="col-md-4">
      <label>Current Image</label><br>
      <img src="/assets/img/<?= htmlspecialchars($product['image']) ?>" style="height:80px;object-fit:cover">
    </div>
    <div class="col-md-4"><label>Change Image</label><input name="image" type="file" accept="image/*" class="form-control"></div>
    <div class="col-12"><button class="btn btn-primary">Save</button> <a class="btn btn-secondary" href="products.php">Back</a></div>
  </form>
</div>
</body>
</html>
