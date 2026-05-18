<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$rows = $conn->query("SELECT b.*, u.name AS uname, u.email, m.title, s.show_date, s.show_time, t.name AS tname FROM bookings b JOIN users u ON b.user_id=u.id JOIN shows s ON b.show_id=s.id JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id ORDER BY b.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Bookings'; require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div>
    <h2 class="section-title">All Bookings</h2>
    <div class="table-wrap"><table>
      <thead><tr><th>Code</th><th>User</th><th>Movie</th><th>Theater</th><th>Show</th><th>Seats</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><?= e($r['booking_code']) ?></td>
        <td><?= e($r['uname']) ?><br><small style="color:var(--muted)"><?= e($r['email']) ?></small></td>
        <td><?= e($r['title']) ?></td><td><?= e($r['tname']) ?></td>
        <td><?= date('M j, g:iA', strtotime($r['show_date'].' '.$r['show_time'])) ?></td>
        <td><?= e($r['seats']) ?></td><td>₹<?= number_format($r['total_amount']) ?></td>
        <td><?= $r['status'] ?></td><td><?= date('M j, Y', strtotime($r['created_at'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
