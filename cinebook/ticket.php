<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT b.*, m.title, m.poster, s.show_date, s.show_time, t.name AS theater_name, t.location, u.name AS user_name FROM bookings b JOIN shows s ON b.show_id=s.id JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id JOIN users u ON b.user_id=u.id WHERE b.id=? AND (b.user_id=? OR ?='admin')");
$role = $_SESSION['role'];
$stmt->bind_param('iis', $id, $_SESSION['user_id'], $role);
$stmt->execute();
$bk = $stmt->get_result()->fetch_assoc();
if (!$bk) { http_response_code(404); die('Booking not found'); }
$pageTitle = 'E-Ticket';
require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align:center;margin-bottom:24px;">
  <div style="display:inline-flex;align-items:center;gap:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);padding:10px 24px;border-radius:50px;color:var(--success);font-weight:700;">
    ✓ Booking Confirmed!
  </div>
</div>

<div class="ticket-wrap">
  <div class="ticket">
    <div class="ticket-top">
      <div style="font-size:2rem;margin-bottom:8px;">🎟</div>
      <div class="ticket-movie"><?= e($bk['title']) ?></div>
      <div style="color:rgba(255,255,255,0.7);font-size:.82rem;margin:6px 0 14px;">Booking Code</div>
      <div class="ticket-code"><?= e($bk['booking_code']) ?></div>
    </div>

    <div class="ticket-divider">
      <div></div>
      <div class="ticket-divider-line"></div>
      <div></div>
    </div>

    <div class="ticket-body">
      <div class="ticket-row">
        <span class="ticket-label">🎭 Theater</span>
        <span class="ticket-value"><?= e($bk['theater_name']) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">📅 Date</span>
        <span class="ticket-value"><?= date('D, M j, Y', strtotime($bk['show_date'])) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">🕐 Time</span>
        <span class="ticket-value"><?= date('g:i A', strtotime($bk['show_time'])) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">💺 Seats</span>
        <span class="ticket-value" style="color:var(--primary)"><?= e($bk['seats']) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">👤 Name</span>
        <span class="ticket-value"><?= e($bk['user_name']) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">💰 Total Paid</span>
        <span class="ticket-value" style="color:var(--success);font-size:1.1rem;">₹<?= number_format($bk['total_amount'],2) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">Status</span>
        <span class="ticket-value" style="color:var(--success);">✓ <?= strtoupper($bk['status']) ?></span>
      </div>
    </div>

    <div class="ticket-footer">
      🎬 Thank you for booking with <?= SITE_NAME ?>! Enjoy the movie.
    </div>
  </div>

  <div style="display:flex;gap:10px;margin-top:20px;justify-content:center;">
    <a href="my_bookings.php" class="btn btn-secondary">📋 My Bookings</a>
    <a href="index.php" class="btn">🎬 Book More</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
