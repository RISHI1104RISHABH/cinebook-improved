<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$stmt = $conn->prepare("SELECT b.*, m.title, s.show_date, s.show_time, t.name AS theater_name FROM bookings b JOIN shows s ON b.show_id=s.id JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id WHERE b.user_id=? ORDER BY b.created_at DESC");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Cancel booking
if (isset($_GET['cancel'])) {
    $bid = (int)$_GET['cancel'];
    $c = $conn->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?");
    $c->bind_param('ii', $bid, $_SESSION['user_id']);
    $c->execute();
    flash('Booking cancelled.');
    header('Location: my_bookings.php'); exit;
}

$pageTitle = 'My Bookings';
require_once __DIR__ . '/includes/header.php';
?>
<h2 class="section-title">My Bookings</h2>
<?php if (empty($rows)): ?>
  <p style="color:var(--muted)">No bookings yet. <a href="index.php" style="color:var(--primary)">Browse movies</a></p>
<?php else: ?>
<div class="table-wrap">
<table>
<thead><tr><th>Code</th><th>Movie</th><th>Theater</th><th>Date & Time</th><th>Seats</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['booking_code']) ?></td>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['theater_name']) ?></td>
    <td><?= date('M j, g:i A', strtotime($r['show_date'].' '.$r['show_time'])) ?></td>
    <td><?= e($r['seats']) ?></td>
    <td>₹<?= number_format($r['total_amount']) ?></td>
    <td><span style="color: <?= $r['status']==='confirmed' ? 'var(--success)' : 'var(--danger)' ?>"><?= strtoupper($r['status']) ?></span></td>
    <td>
      <a href="ticket.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">View</a>
      <?php if ($r['status']==='confirmed' && strtotime($r['show_date']) >= strtotime(date('Y-m-d'))): ?>
        <a href="?cancel=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?')">Cancel</a>
      <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
