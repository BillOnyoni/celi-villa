<?php
// src/admin/index.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if ($row['role'] !== 'admin') {
            $error = "Not an admin account.";
        } elseif (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            redirect('dashboard.php');
        } else {
            $error = "Invalid credentials.";
        }
    } else $error = "User not found.";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login - Celica Computers Villa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
  <div class="container" style="max-width:420px;margin-top:80px;">
    <div class="card shadow">
      <div class="card-body">
        <div class="text-center mb-4">
          <i class="fas fa-shield-alt fa-3x text-primary"></i>
          <h4 class="card-title mt-3">Admin Login</h4>
        </div>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
          </div>
          <div class="d-grid">
            <button class="btn btn-primary">Login to Admin Panel</button>
          </div>
        </form>
        <div class="text-center mt-3">
          <a href="/public/index.php" class="text-decoration-none small">← Back to Website</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>