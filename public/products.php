<?php
// public/products.php
require_once __DIR__ . '/../src/includes/header.php';

$search = sanitize($_GET['q'] ?? '');
$category = (int)($_GET['category'] ?? 0);

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Products</h2>
    </div>
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <select name="category" class="form-select me-2" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>

<div class="row g-4">
    <?php if ($products->num_rows === 0): ?>
    <div class="col-12">
        <div class="alert alert-info text-center">
            <h4>No products found</h4>
            <p>Try adjusting your search or browse all products.</p>
            <a href="/public/products.php" class="btn btn-primary">View All Products</a>
        </div>
    </div>
    <?php else: ?>
    <?php while ($product = $products->fetch_assoc()): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card h-100 shadow-sm product-card">
            <img src="/public/assets/img/<?= htmlspecialchars($product['image']) ?>" 
                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                <?php if ($product['category_name']): ?>
                <small class="text-muted mb-2"><?= htmlspecialchars($product['category_name']) ?></small>
                <?php endif; ?>
                <p class="card-text flex-grow-1 small"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h6 text-primary mb-0">KES <?= formatPrice($product['price']) ?></span>
                    <a href="/public/cart.php?add=<?= $product['product_id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-cart-plus"></i> Add
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</div>

<style>
.product-card {
    transition: transform 0.2s ease-in-out;
}
.product-card:hover {
    transform: translateY(-5px);
}
</style>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>