<?php
// register.php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $pass = $_POST['password'];
    $cpass = $_POST['cpassword'];

    if ($pass !== $cpass) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email.";
    } else {
        // check existing
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $error = "Email already registered.";
        else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO users (username,email,phone,password) VALUES (?,?,?,?)");
            $ins->bind_param("ssss", $username, $email, $phone, $hash);
            if ($ins->execute()) {
                // send welcome email
                $subject = "Welcome to Celica Computers Villa";
                $message = "Hello $username,\n\nThanks for registering at Celica Computers Villa. Visit us at Moi Avenue, Nairobi.\n\nBest,\nCelica Team";
                sendEmail($email, $subject, $message);
                redirect("login.php?registered=1");
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<h2>Register</h2>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<form method="POST" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Username</label>
    <input name="username" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Phone</label>
    <input name="phone" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Password</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Confirm Password</label>
    <input name="cpassword" type="password" class="form-control" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Register</button>
  </div>
</form>

<?php require_once 'includes/footer.php'; ?>
