<?php
/**
 * Run this ONCE to set up the admin password.
 * Then either delete this file or it will auto-disable after use.
 */
require_once __DIR__ . '/includes/config.php';

// Check if already installed
$check = $conn->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
$check->execute();
$admin = $check->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $pw = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$pw' WHERE role='admin'");
    echo '<p style="font-family:sans-serif;color:green;">✅ Admin password updated! <a href="login.php">Login now</a>. <strong>Delete install.php!</strong></p>';
    exit;
}
?>
<!DOCTYPE html>
<html><head><title>Install CineBook</title>
<style>body{font-family:sans-serif;max-width:420px;margin:60px auto;padding:0 20px;}
input{width:100%;padding:10px;margin:8px 0 16px;border:1px solid #ccc;border-radius:6px;font-size:1rem;}
button{background:#e50914;color:#fff;border:none;padding:12px 24px;border-radius:6px;cursor:pointer;font-size:1rem;width:100%;}
.warn{background:#fff3cd;border:1px solid #ffc107;padding:12px;border-radius:6px;margin-bottom:16px;font-size:.9rem;}
</style></head>
<body>
<h2>🎬 CineBook Setup</h2>
<div class="warn">⚠️ Delete this file after running it once.</div>
<?php if ($admin): ?>
<p>Admin account exists. Set a new password:</p>
<form method="post">
  <label>New Admin Password</label>
  <input type="password" name="new_password" minlength="6" required>
  <button>Update Admin Password</button>
</form>
<?php else: ?>
<p style="color:red;">No admin account found. Please import <code>sql/database.sql</code> first.</p>
<?php endif; ?>
</body></html>
