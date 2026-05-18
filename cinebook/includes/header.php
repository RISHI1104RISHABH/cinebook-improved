<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
<meta name="description" content="Book movie tickets online — best seats, instant confirmation.">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎬</text></svg>">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a class="brand" href="<?= SITE_URL ?>/index.php">🎬 <?= SITE_NAME ?></a>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">☰</button>
    <ul class="nav-links" id="navLinks">
      <li><a href="<?= SITE_URL ?>/index.php">Now Showing</a></li>
      <li><a href="<?= SITE_URL ?>/index.php?status=upcoming">Upcoming</a></li>
      <?php if (isLoggedIn()): ?>
        <li><a href="<?= SITE_URL ?>/my_bookings.php">My Bookings</a></li>
        <?php if (isAdmin()): ?>
          <li><a href="<?= SITE_URL ?>/admin/index.php" class="nav-admin">⚙ Admin</a></li>
        <?php endif; ?>
        <li><a href="<?= SITE_URL ?>/logout.php" class="nav-logout">Sign Out</a></li>
      <?php else: ?>
        <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
        <li><a href="<?= SITE_URL ?>/register.php" class="nav-signup">Sign Up</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<div class="main-content">
<?= flash() ?>
<script>
document.getElementById('navToggle').addEventListener('click',function(){
  document.getElementById('navLinks').classList.toggle('open');
});
</script>
