<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Celica Computers Villa</title>
  <!-- Bootstrap -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="<?php echo (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? 'dark-mode' : ''; ?>">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="/index.php">Celica Computers Villa</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="/contact.php">Contact</a></li>
      </ul>

      <form class="d-flex me-3" method="GET" action="/products.php">
        <input class="form-control me-2" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        <button class="btn btn-outline-success" type="submit"><i class="fa fa-search"></i></button>
      </form>

      <ul class="navbar-nav">
        <?php if (isset($_SESSION['user_id'])): 
           $cartCount = getCartCount();
        ?>
          <li class="nav-item"><a class="nav-link" href="/cart.php"><i class="fa fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/settings.php">Settings</a></li>
              <li><a class="dropdown-item" href="/account.php">My Account</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
