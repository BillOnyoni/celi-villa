<?php
// reset_password.php
require_once 'includes/header.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    echo "<div class='alert alert-danger'>Invalid token.</div>";
    require_once 'includes/footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT pr.user_id, u.email, pr.expires_at FROM password_resets pr JOIN users u ON pr.user_id = u.user_id WHERE pr.token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (new DateTime() > new DateTime($row['expires_at'])) {
        echo "<div class='alert alert-danger'>Token expired.</div>";
        require_once 'includes/footer.php';
        exit();
    }
    $user_id = $row['user_id'];
} else {
    echo "<div class='alert alert-danger'>Invalid token.</div>";
    require_once 'includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'];
    $cpass = $_POST['cpassword'];
    if ($pass !== $cpass) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $upd->bind_param("si", $hash, $user_id);
        if ($upd->execute()) {
            // remove token
            $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $del->bind_param("s", $token);
            $del->execute();
            redirect('/login.php?reset=1');
        } else {
            $error = "Failed to reset password.";
        }
    }
}
?>

<h2>Reset Password</h2>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST" class="row g-3">
  <div class="col-md-6">
    <input name="password" type="password" class="form-control" placeholder="New password" required>
  </div>
  <div class="col-md-6">
    <input name="cpassword" type="password" class="form-control" placeholder="Confirm password" required>
  </div>
  <div class="col-12">
    <button class="btn btn-success">Reset Password</button>
  </div>
</form>

<?php require_once 'includes/footer.php'; ?>
