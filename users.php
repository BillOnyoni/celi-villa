<?php
// admin/users.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // do not allow deleting self
    if ($delId !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delId);
        $stmt->execute();
    }
    redirect('users.php');
}

$res = $conn->query("SELECT user_id, username, email, phone, role, created_at FROM users ORDER BY created_at DESC");
?>
<!doctype html><html><head><meta charset="utf-8"><title>Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4">
  <h3>Users</h3>
  <table class="table table-hover">
    <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Phone</th><th>Role</th><th>Created</th><th>Action</th></tr></thead>
    <tbody>
      <?php while($u=$res->fetch_assoc()): ?>
      <tr>
        <td><?= $u['user_id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone']) ?></td>
        <td><?= $u['role'] ?></td>
        <td><?= $u['created_at'] ?></td>
        <td>
          <?php if ($u['user_id'] !== $_SESSION['user_id']): ?>
            <a class="btn btn-sm btn-danger" href="?delete=<?= $u['user_id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
          <?php else: ?>
            <span class="text-muted">You</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body></html>
