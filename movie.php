<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM movies WHERE id=?");
$stmt->bind_param('i', $id); $stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
if (!$movie) { http_response_code(404); die('Movie not found'); }
$pageTitle = $movie['title'];

$stmt = $conn->prepare("SELECT s.*, t.name AS theater_name, t.location FROM shows s JOIN theaters t ON s.theater_id=t.id WHERE s.movie_id=? AND s.show_date >= CURDATE() ORDER BY s.show_date, s.show_time");
$stmt->bind_param('i', $id); $stmt->execute();
$shows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$grouped = [];
foreach ($shows as $s) { $grouped[$s['show_date']][] = $s; }
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom:20px;">
  <a href="index.php" class="btn btn-ghost btn-sm">← Back to Movies</a>
</div>

<div class="movie-detail">
  <div class="movie-detail-poster">
    <img src="<?= e($movie['poster']) ?>" alt="<?= e($movie['title']) ?>"
         onerror="this.src='https://placehold.co/300x450/13131f/e50914?text=<?= rawurlencode($movie['title']) ?>'">
  </div>
  <div class="movie-detail-info">
    <div style="margin-bottom:10px;">
      <?php if ($movie['status']==='upcoming'): ?>
        <span class="badge badge-upcoming">🗓 Upcoming</span>
      <?php else: ?>
        <span class="badge badge-showing">▶ Now Showing</span>
      <?php endif; ?>
    </div>
    <h1><?= e($movie['title']) ?></h1>
    <div class="detail-meta">
      <?php if ($movie['rating'] > 0): ?>
      <div class="detail-meta-item"><span class="icon">⭐</span> <strong style="color:var(--gold)"><?= number_format($movie['rating'],1) ?></strong>/10</div>
      <?php endif; ?>
      <div class="detail-meta-item"><span class="icon">🕐</span> <?= formatDuration((int)$movie['duration']) ?></div>
      <div class="detail-meta-item"><span class="icon">🌐</span> <?= e($movie['language']) ?></div>
      <div class="detail-meta-item"><span class="icon">📅</span> <?= date('M j, Y', strtotime($movie['release_date'])) ?></div>
    </div>
    <div style="margin-bottom:18px;">
      <?php foreach (explode(',', $movie['genre'] ?? '') as $g): ?>
        <span class="genre-pill" style="font-size:.8rem;padding:5px 12px;"><?= e(trim($g)) ?></span>
      <?php endforeach; ?>
    </div>
    <p class="movie-detail-desc"><?= nl2br(e($movie['description'])) ?></p>
    <?php if ($movie['trailer_url']): ?>
      <a class="btn btn-secondary" target="_blank" href="<?= e($movie['trailer_url']) ?>">▶ Watch Trailer</a>
    <?php endif; ?>
  </div>
</div>

<div class="shows-section">
  <h2 class="section-title">🎟 Available Showtimes</h2>
  <?php if (empty($grouped)): ?>
    <div class="empty">
      <div class="empty-icon">🗓</div>
      <h3>No shows scheduled</h3>
      <p>Check back later for upcoming showtimes.</p>
    </div>
  <?php else: ?>
    <?php foreach ($grouped as $date => $list): ?>
      <div class="show-date-group">
        <div class="show-date-label"><?= date('l, F j, Y', strtotime($date)) ?></div>
        <div class="shows-grid">
          <?php foreach ($list as $s): ?>
            <a class="show-btn" href="booking.php?show_id=<?= $s['id'] ?>">
              <span class="show-btn-time"><?= date('g:i A', strtotime($s['show_time'])) ?></span>
              <span class="show-btn-info"><?= e($s['theater_name']) ?> · ₹<?= number_format($s['price']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
