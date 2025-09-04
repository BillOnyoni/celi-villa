<?php
// public/login.php
require_once __DIR__ . '/../src/includes/header.php';

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
            if ($row['role'] === 'admin') redirect('/src/admin/dashboard.php');
            redirect('/public/index.php');
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Login</h2>
                <?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Registered successfully. Please login.</div><?php endif; ?>
                <?php if (isset($_GET['reset'])): ?><div class="alert alert-success">Password reset successfully. Please login with your new password.</div><?php endif; ?>
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
                        <button class="btn btn-primary" type="submit">Login</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="/public/forgot-password.php" class="text-decoration-none">Forgot password?</a>
                    <span class="mx-2">|</span>
                    <a href="/public/register.php" class="text-decoration-none">Create account</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>