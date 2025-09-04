<?php
// settings.php
require_once 'includes/header.php';
if (!isset($_SESSION['user_id'])) redirect('/login.php');

if (isset($_GET['toggle_dark'])) {
    $_SESSION['dark_mode'] = !($_SESSION['dark_mode'] ?? false);
    redirect('/settings.php');
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

<h2>Settings</h2>
<?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<a class="btn btn-outline-secondary toggle-dark">Toggle Dark Mode</a>

<form method="POST" class="row g-3 mt-3">
  <div class="col-md-6"><label>Phone</label><input name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control"></div>
  <div class="col-12"><label>Address</label><textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea></div>
  <div class="col-12"><button class="btn btn-primary">Save</button></div>
</form>

<?php require_once 'includes/footer.php'; ?>
