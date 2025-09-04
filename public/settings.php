<?php
// public/settings.php
require_once __DIR__ . '/../src/includes/header.php';
if (!isset($_SESSION['user_id'])) redirect('/public/login.php');

if (isset($_GET['toggle_dark'])) {
    $_SESSION['dark_mode'] = !($_SESSION['dark_mode'] ?? false);
    redirect('/public/settings.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update profile
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $stmt = $conn->prepare("UPDATE users SET address = ?, phone = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $address, $phone, $_SESSION['user_id']);
    $stmt->execute();
    $success = "Profile updated.";
}
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="row">
    <div class="col-md-8">
        <h2>Settings</h2>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input value="<?= htmlspecialchars($user['username']) ?>" class="form-control" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input value="<?= htmlspecialchars($user['email']) ?>" class="form-control" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Preferences</h6>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-secondary toggle-dark w-100">
                    <i class="fas fa-moon"></i> Toggle Dark Mode
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>