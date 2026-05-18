<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) { $conn->query("DELETE FROM users WHERE id=$id"); flash('User deleted'); }
    header('Location: users.php'); exit;
}
$rows = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Users'; require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div>
    <h2 class="section-title">Users</h2>
    <div class="table-wrap"><table>
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $u): ?>
        <tr><td><?= $u['id'] ?></td><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td>
        <td><?= e($u['phone']) ?></td><td><?= e($u['role']) ?></td>
        <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
        <td><?php if ($u['id']!=$_SESSION['user_id']): ?>
          <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')">Delete</a>
        <?php endif; ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
