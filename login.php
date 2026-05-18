<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param('s', $email); $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['name']    = $u['name'];
        $_SESSION['role']    = $u['role'];
        flash('Welcome back, ' . $u['name'] . '!');
        header('Location: ' . ($u['role']==='admin' ? SITE_URL.'/admin/index.php' : SITE_URL.'/index.php')); exit;
    }
    $error = 'Invalid email or password.';
}
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-box">
  <h2>Welcome back 👋</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrfField() ?>
    <div class="form-group"><label>Email</label><input name="email" type="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="form-group"><label>Password</label><input name="password" type="password" required autocomplete="current-password"></div>
    <button class="btn btn-block" style="margin-top:4px;">Login</button>
  </form>
  <p style="text-align:center;margin-top:20px;color:var(--muted);">
    Don't have an account? <a href="register.php" style="color:var(--primary);">Sign Up</a>
  </p>
  <p style="text-align:center;margin-top:8px;color:var(--muted);font-size:.82rem;">
    Demo admin: <code>admin@cinema.com</code> / <code>admin123</code>
  </p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
