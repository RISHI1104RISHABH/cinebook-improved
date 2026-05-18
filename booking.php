<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$show_id = (int)($_GET['show_id'] ?? 0);
$stmt = $conn->prepare("SELECT s.*, m.title, m.poster, t.name AS theater_name, t.total_seats FROM shows s JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id WHERE s.id=?");
$stmt->bind_param('i', $show_id); $stmt->execute();
$show = $stmt->get_result()->fetch_assoc();
if (!$show) { http_response_code(404); die('Show not found'); }
$taken = bookedSeats($conn, $show_id);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $seats = $_POST['seats'] ?? '';
    $seatArr = array_filter(array_map('trim', explode(',', $seats)));
    if (empty($seatArr)) $error = 'Please select at least one seat.';
    elseif (array_intersect($seatArr, $taken)) $error = 'Some seats were just booked. Please reselect.';
    else {
        $total = count($seatArr) * $show['price'];
        $seatStr = implode(',', $seatArr);
        $code = generateBookingCode();
        $cnt = count($seatArr);
        $ins = $conn->prepare("INSERT INTO bookings (user_id, show_id, seats, total_seats, total_amount, booking_code) VALUES (?,?,?,?,?,?)");
        $ins->bind_param('iisids', $_SESSION['user_id'], $show_id, $seatStr, $cnt, $total, $code);
        $ins->execute();
        flash('🎉 Booking confirmed! Your code: ' . $code);
        header('Location: ticket.php?id=' . $ins->insert_id); exit;
    }
}
$pageTitle = 'Book — ' . $show['title'];
require_once __DIR__ . '/includes/header.php';
$rows = ['A','B','C','D','E','F']; $cols = 10;
?>

<div class="booking-header">
  <img src="<?= e($show['poster']) ?>" alt="<?= e($show['title']) ?>"
       onerror="this.src='https://placehold.co/60x90/13131f/e50914?text=?'">
  <div>
    <h2><?= e($show['title']) ?></h2>
    <p>📍 <?= e($show['theater_name']) ?> &nbsp;·&nbsp; 📅 <?= date('D, M j', strtotime($show['show_date'])) ?> at <?= date('g:i A', strtotime($show['show_time'])) ?> &nbsp;·&nbsp; 💰 ₹<?= number_format($show['price']) ?>/seat</p>
  </div>
</div>

<?php if ($error): ?><div class="alert alert-danger">⚠ <?= e($error) ?></div><?php endif; ?>

<div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:24px;">
  <div class="screen-wrap">
    <div class="screen-bar"></div>
    <div class="screen-label">S C R E E N</div>
  </div>

  <div class="seat-legend">
    <div class="legend-item"><div class="legend-box avail"></div> Available</div>
    <div class="legend-item"><div class="legend-box sel"></div> Selected</div>
    <div class="legend-item"><div class="legend-box taken"></div> Booked</div>
  </div>

  <div class="seats-container">
    <?php foreach ($rows as $r): ?>
      <div class="seat-row">
        <span class="row-label"><?= $r ?></span>
        <?php for ($c=1; $c<=5; $c++):
          $id = $r.$c; $isTaken = in_array($id, $taken); ?>
          <div class="seat<?= $isTaken?' taken':'' ?>" data-seat="<?= $id ?>"><?= $c ?></div>
        <?php endfor; ?>
        <div class="aisle"></div>
        <?php for ($c=6; $c<=$cols; $c++):
          $id = $r.$c; $isTaken = in_array($id, $taken); ?>
          <div class="seat<?= $isTaken?' taken':'' ?>" data-seat="<?= $id ?>"><?= $c ?></div>
        <?php endfor; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<form method="post" id="bookForm">
  <?= csrfField() ?>
  <input type="hidden" name="seats" id="seatsInput">
  <div class="booking-summary">
    <h3>Booking Summary</h3>
    <div class="summary-row"><span>Selected Seats</span><span id="selectedList" style="color:var(--text);font-weight:700;">None</span></div>
    <div class="summary-row"><span>Tickets</span><span><span id="count">0</span> × ₹<?= number_format($show['price']) ?></span></div>
    <div class="summary-total">₹<span id="total">0</span></div>
    <button class="btn btn-block btn-lg" type="submit" id="payBtn" disabled>🎟 Confirm Booking</button>
    <p style="color:var(--muted);font-size:.75rem;margin-top:12px;">Tickets are non-refundable after 30 minutes of booking.</p>
  </div>
</form>

<script>
const price = <?= (float)$show['price'] ?>;
const selected = new Set();
document.querySelectorAll('.seat:not(.taken)').forEach(el => {
  el.addEventListener('click', () => {
    const s = el.dataset.seat;
    if (selected.has(s)) { selected.delete(s); el.classList.remove('selected'); }
    else { selected.add(s); el.classList.add('selected'); }
    update();
  });
});
function update() {
  const arr = [...selected].sort();
  document.getElementById('selectedList').textContent = arr.length ? arr.join(', ') : 'None';
  document.getElementById('count').textContent = arr.length;
  document.getElementById('total').textContent = (arr.length * price).toLocaleString('en-IN');
  document.getElementById('seatsInput').value = arr.join(',');
  document.getElementById('payBtn').disabled = arr.length === 0;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
