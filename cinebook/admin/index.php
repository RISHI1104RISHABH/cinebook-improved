<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$stats = [
  'movies'   => $conn->query("SELECT COUNT(*) c FROM movies")->fetch_assoc()['c'],
  'shows'    => $conn->query("SELECT COUNT(*) c FROM shows")->fetch_assoc()['c'],
  'bookings' => $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='confirmed'")->fetch_assoc()['c'],
  'users'    => $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'],
  'revenue'  => $conn->query("SELECT COALESCE(SUM(total_amount),0) s FROM bookings WHERE status='confirmed'")->fetch_assoc()['s'],
];
$recent = $conn->query("SELECT b.booking_code, b.total_amount, b.created_at, m.title, u.name AS user_name FROM bookings b JOIN shows s ON b.show_id=s.id JOIN movies m ON s.movie_id=m.id JOIN users u ON b.user_id=u.id WHERE b.status='confirmed' ORDER BY b.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-wrap">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="admin-content">
    <h2 class="section-title">📊 Dashboard</h2>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon">🎬</div><div class="stat-num"><?= $stats['movies'] ?></div><div class="stat-label">Movies</div></div>
      <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-num"><?= $stats['shows'] ?></div><div class="stat-label">Shows</div></div>
      <div class="stat-card"><div class="stat-icon">🎟</div><div class="stat-num"><?= $stats['bookings'] ?></div><div class="stat-label">Bookings</div></div>
      <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-num"><?= $stats['users'] ?></div><div class="stat-label">Users</div></div>
      <div class="stat-card" style="border-color:rgba(229,9,20,0.4)"><div class="stat-icon">💰</div><div class="stat-num" style="font-size:1.4rem;">₹<?= number_format($stats['revenue']) ?></div><div class="stat-label">Revenue</div></div>
    </div>

    <h3 class="section-title">🕐 Recent Bookings</h3>
    <?php if (empty($recent)): ?>
      <div class="empty"><div class="empty-icon">📭</div><h3>No bookings yet</h3></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Code</th><th>Customer</th><th>Movie</th><th>Amount</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $b): ?>
          <tr>
            <td><code style="color:var(--primary);font-weight:700;"><?= e($b['booking_code']) ?></code></td>
            <td><?= e($b['user_name']) ?></td>
            <td><?= e($b['title']) ?></td>
            <td style="color:var(--success);font-weight:700;">₹<?= number_format($b['total_amount']) ?></td>
            <td style="color:var(--muted);"><?= date('M j, g:i A', strtotime($b['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
