<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($name) < 2)         $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($password) < 6)     $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)    $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param('s', $email); $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'That email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)");
            $ins->bind_param('ssss', $name, $email, $phone, $hash);
            $ins->execute();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $ins->insert_id;
            $_SESSION['name']    = $name;
            $_SESSION['role']    = 'user';
            flash('Account created! Welcome, ' . $name . ' 🎉');
            header('Location: ' . SITE_URL . '/index.php'); exit;
        }
    }
}
$pageTitle = 'Sign Up';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-box">
  <h2>Create Account 🍿</h2>
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post">
    <?= csrfField() ?>
    <div class="form-group"><label>Full Name</label><input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
    <div class="form-group"><label>Email</label><input name="email" type="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="form-group"><label>Phone (optional)</label><input name="phone" type="tel" value="<?= e($_POST['phone'] ?? '') ?>"></div>
    <div class="form-group"><label>Password</label><input name="password" type="password" required minlength="6"></div>
    <div class="form-group"><label>Confirm Password</label><input name="confirm" type="password" required></div>
    <button class="btn btn-block" style="margin-top:4px;">Create Account</button>
  </form>
  <p style="text-align:center;margin-top:20px;color:var(--muted);">
    Already have an account? <a href="login.php" style="color:var(--primary);">Login</a>
  </p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
