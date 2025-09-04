<?php
// login.php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            if ($row['role'] === 'admin') redirect('/admin/dashboard.php');
            redirect('/index.php');
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
?>

<h2>Login</h2>
<?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Registered successfully. Please login.</div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<form method="POST" class="row g-3">
  <div class="col-md-6"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
  <div class="col-md-6"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Login</button>
    <a href="/forgot_password.php" class="btn btn-link">Forgot password?</a>
  </div>
</form>

<?php require_once 'includes/footer.php'; ?>
