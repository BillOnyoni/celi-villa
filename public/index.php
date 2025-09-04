<?php
// public/index.php
require_once __DIR__ . '/../src/includes/header.php';

// Fetch featured products
$featured = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
?>

<div class="hero-section bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Welcome to Celica Computers Villa</h1>
                <p class="lead">Your trusted technology partner in Nairobi. Quality computers, accessories, and electronics at competitive prices.</p>
                <a href="/public/products.php" class="btn btn-light btn-lg">Shop Now</a>
            </div>
            <div class="col-md-6 text-center">
                <i class="fas fa-laptop fa-10x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row g-4">
        <?php while ($product = $featured->fetch_assoc()): ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <img src="/public/assets/img/<?= htmlspecialchars($product['image']) ?>" 
                     class="card-img-top" style="height: 200px; object-fit: cover;" 
                     alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                    <p class="card-text flex-grow-1"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 text-primary mb-0">KES <?= formatPrice($product['price']) ?></span>
                        <a href="/public/cart.php?add=<?= $product['product_id'] ?>" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>