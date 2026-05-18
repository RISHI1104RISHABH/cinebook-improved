<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $fields = ['title','description','genre','language','poster','trailer_url','release_date','status'];
    $data = []; foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $rating = (float)($_POST['rating'] ?? 0);
    if ($id) {
        $stmt = $conn->prepare("UPDATE movies SET title=?,description=?,genre=?,language=?,duration=?,rating=?,poster=?,trailer_url=?,release_date=?,status=? WHERE id=?");
        $stmt->bind_param('ssssidssssi', $data['title'],$data['description'],$data['genre'],$data['language'],$duration,$rating,$data['poster'],$data['trailer_url'],$data['release_date'],$data['status'],$id);
        $stmt->execute(); flash('Movie updated');
    } else {
        $stmt = $conn->prepare("INSERT INTO movies (title,description,genre,language,duration,rating,poster,trailer_url,release_date,status) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssidssss', $data['title'],$data['description'],$data['genre'],$data['language'],$duration,$rating,$data['poster'],$data['trailer_url'],$data['release_date'],$data['status']);
        $stmt->execute(); flash('Movie added');
    }
    header('Location: movies.php'); exit;
}
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM movies WHERE id=" . (int)$_GET['delete']);
    flash('Movie deleted'); header('Location: movies.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id=?");
    $stmt->bind_param('i', $_GET['edit']); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}
$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Manage Movies';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div>
    <h2 class="section-title"><?= $edit ? 'Edit' : 'Add' ?> Movie</h2>
    <form method="post" style="background:var(--surface); padding:20px; border-radius:10px; margin-bottom:30px;">
      <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
      <div class="form-group"><label>Title</label><input name="title" required value="<?= e($edit['title'] ?? '') ?>"></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea></div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div class="form-group"><label>Genre</label><input name="genre" value="<?= e($edit['genre'] ?? '') ?>"></div>
        <div class="form-group"><label>Language</label><input name="language" value="<?= e($edit['language'] ?? '') ?>"></div>
        <div class="form-group"><label>Duration (min)</label><input name="duration" type="number" value="<?= e($edit['duration'] ?? '') ?>"></div>
        <div class="form-group"><label>Rating</label><input name="rating" type="number" step="0.1" value="<?= e($edit['rating'] ?? '') ?>"></div>
        <div class="form-group"><label>Release Date</label><input name="release_date" type="date" value="<?= e($edit['release_date'] ?? '') ?>"></div>
        <div class="form-group"><label>Status</label>
          <select name="status">
            <option value="now_showing" <?= ($edit['status']??'')==='now_showing'?'selected':'' ?>>Now Showing</option>
            <option value="upcoming" <?= ($edit['status']??'')==='upcoming'?'selected':'' ?>>Upcoming</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Poster URL</label><input name="poster" value="<?= e($edit['poster'] ?? '') ?>"></div>
      <div class="form-group"><label>Trailer URL</label><input name="trailer_url" value="<?= e($edit['trailer_url'] ?? '') ?>"></div>
      <button class="btn"><?= $edit ? 'Update' : 'Add' ?> Movie</button>
      <?php if ($edit): ?> <a href="movies.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
    </form>

    <h2 class="section-title">All Movies</h2>
    <div class="table-wrap"><table>
      <thead><tr><th>ID</th><th>Title</th><th>Genre</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($movies as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td><td><?= e($m['title']) ?></td><td><?= e($m['genre']) ?></td>
          <td><?= $m['duration'] ?> min</td><td><?= e($m['status']) ?></td>
          <td>
            <a href="?edit=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <a href="?delete=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
