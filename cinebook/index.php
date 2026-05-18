<?php
$pageTitle = 'Movies';
require_once __DIR__ . '/includes/header.php';

$status = in_array($_GET['status'] ?? '', ['now_showing','upcoming']) ? $_GET['status'] : 'now_showing';
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM movies WHERE status=?";
$params = [$status]; $types = 's';
if ($search !== '') {
    $sql .= " AND (title LIKE ? OR genre LIKE ? OR language LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $types .= 'sss';
}
$sql .= " ORDER BY release_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="hero">
  <h1>🍿 Book Your Movie Tickets</h1>
  <p>Latest blockbusters · Best seats · Instant confirmation</p>
  <form class="search-bar" method="get" style="position:relative;z-index:1;">
    <input type="hidden" name="status" value="<?= e($status) ?>">
    <input type="text" name="q" placeholder="Search by title, genre, language…" value="<?= e($search) ?>" autofocus>
    <button type="submit" class="btn">Search</button>
  </form>
</div>

<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
  <div class="filter-tabs">
    <a href="?status=now_showing" class="filter-tab <?= $status==='now_showing' ? 'active' : '' ?>">🎬 Now Showing</a>
    <a href="?status=upcoming"   class="filter-tab <?= $status==='upcoming'    ? 'active' : '' ?>">🗓 Upcoming</a>
  </div>
  <?php if ($search): ?>
    <span style="color:var(--muted);font-size:.9rem;">
      <?= count($movies) ?> result<?= count($movies) !== 1 ? 's' : '' ?> for "<strong><?= e($search) ?></strong>"
      <a href="?status=<?= e($status) ?>" style="color:var(--primary);margin-left:8px;">✕ Clear</a>
    </span>
  <?php endif; ?>
</div>

<?php if (empty($movies)): ?>
<div class="empty">
  <div class="icon">🎭</div>
  <h3>No movies found</h3>
  <p>Try a different search or check back later.</p>
</div>
<?php else: ?>
<div class="movie-grid">
  <?php foreach ($movies as $m): ?>
  <a class="movie-card" href="movie.php?id=<?= $m['id'] ?>">
    <div class="movie-card-poster">
      <img src="<?= e($m['poster'] ?: '') ?>"
           alt="<?= e($m['title']) ?>"
           onerror="this.src='https://placehold.co/300x450/1a1a2e/e50914?text=<?= rawurlencode($m['title']) ?>'">
      <div class="movie-card-overlay">
        <span class="book-now"><?= $m['status']==='upcoming' ? 'View Details' : 'Book Now' ?></span>
      </div>
    </div>
    <div class="movie-card-body">
      <h3><?= e($m['title']) ?></h3>
      <div class="meta">
        <?php foreach (explode(',', $m['genre'] ?? '') as $g): ?>
          <span class="genre-tag" style="font-size:.72rem;padding:2px 8px;"><?= e(trim($g)) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="meta" style="margin-top:4px;">
        <?php if ($m['rating'] > 0): ?>
          <span class="rating">★ <?= number_format($m['rating'],1) ?></span> ·
        <?php endif; ?>
        <?= formatDuration((int)$m['duration']) ?> · <?= e($m['language']) ?>
      </div>
      <?php if ($m['status']==='upcoming'): ?>
        <span class="badge badge-upcoming">Upcoming</span>
      <?php else: ?>
        <span class="badge badge-showing">Now Showing</span>
      <?php endif; ?>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
