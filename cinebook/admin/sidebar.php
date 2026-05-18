<?php $active = basename($_SERVER['SCRIPT_NAME'], '.php'); ?>
<aside class="admin-sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Main</div>
    <a href="index.php" class="<?= $active==='index'?'active':'' ?>">📊 Dashboard</a>
    <a href="movies.php" class="<?= $active==='movies'?'active':'' ?>">🎬 Movies</a>
    <a href="theaters.php" class="<?= $active==='theaters'?'active':'' ?>">🏛 Theaters</a>
    <a href="shows.php" class="<?= $active==='shows'?'active':'' ?>">📅 Shows</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Reports</div>
    <a href="bookings.php" class="<?= $active==='bookings'?'active':'' ?>">🎟 Bookings</a>
    <a href="users.php" class="<?= $active==='users'?'active':'' ?>">👥 Users</a>
  </div>
  <div class="sidebar-section" style="margin-top:auto;padding-top:16px;border-top:1px solid var(--border);">
    <a href="../index.php">← Back to Site</a>
  </div>
</aside>
