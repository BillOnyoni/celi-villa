<?php
// forgot_password.php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_id = $row['user_id'];
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // store token
        $ins = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $ins->bind_param("iss", $user_id, $token, $expires);
        if ($ins->execute()) {
            $resetLink = "http://localhost/reset_password.php?token=$token"; // adjust host
            $subject = "Password Reset - Celica Computers Villa";
            $message = "Hi {$row['username']},\n\nClick the link to reset your password (valid 1 hour): $resetLink\n\nIf you didn't request this, ignore this email.";
            sendEmail($email, $subject, $message);
            $success = "Reset link sent to your email.";
        } else {
            $error = "Failed to create reset token.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<h2>Forgot Password</h2>
<?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST" class="row g-3">
  <div class="col-md-6">
    <input name="email" type="email" class="form-control" placeholder="Enter your email" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Send reset link</button>
  </div>
</form>

<?php require_once 'includes/footer.php'; ?>
