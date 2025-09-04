<?php
// public/reset-password.php
require_once __DIR__ . '/../src/includes/header.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    echo "<div class='alert alert-danger'>Invalid token.</div>";
    require_once __DIR__ . '/../src/includes/footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT pr.user_id, u.email, pr.expires_at FROM password_resets pr JOIN users u ON pr.user_id = u.user_id WHERE pr.token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (new DateTime() > new DateTime($row['expires_at'])) {
        echo "<div class='alert alert-danger'>Token expired.</div>";
        require_once __DIR__ . '/../src/includes/footer.php';
        exit();
    }
    $user_id = $row['user_id'];
} else {
    echo "<div class='alert alert-danger'>Invalid token.</div>";
    require_once __DIR__ . '/../src/includes/footer.php';
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
            redirect('/public/login.php?reset=1');
        } else {
            $error = "Failed to reset password.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Reset Password</h2>
                <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input name="cpassword" type="password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-success">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>