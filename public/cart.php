<?php
// public/cart.php
require_once __DIR__ . '/../src/includes/header.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Add via GET ?add=ID
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    redirect('/public/cart.php');
}

// Update quantities (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $pid => $q) {
        $pid = (int)$pid;
        $q = max(0, (int)$q);
        if ($q === 0) unset($_SESSION['cart'][$pid]);
        else $_SESSION['cart'][$pid] = $q;
    }
    $success = "Cart updated.";
}

// Fetch product details
$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $pid = $row['product_id'];
        $row['qty'] = $cart[$pid];
        $row['subtotal'] = $row['price'] * $row['qty'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>

<h2>Your Cart</h2>
<?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if (empty($items)): ?>
  <div class="alert alert-info">Your cart is empty. <a href="/public/products.php">Shop now</a></div>
<?php else: ?>
  <form method="POST">
    <table class="table">
      <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?php echo htmlspecialchars($it['name']); ?></td>
          <td>KES <?php echo formatPrice($it['price']); ?></td>
          <td><input type="number" name="qty[<?php echo $it['product_id']; ?>]" value="<?php echo $it['qty']; ?>" min="0" class="form-control" style="width:100px"></td>
          <td>KES <?php echo formatPrice($it['subtotal']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th>KES <?php echo formatPrice($total); ?></th></tr>
      </tfoot>
    </table>

    <div class="d-flex justify-content-between">
      <button name="update_cart" class="btn btn-primary">Update Cart</button>
      <a href="/public/checkout.php" class="btn btn-success">Proceed to Checkout (M-Pesa)</a>
    </div>
  </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>