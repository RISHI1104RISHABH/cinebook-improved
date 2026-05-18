<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['id'] ?? 0);
    $mid = (int)$_POST['movie_id']; $tid = (int)$_POST['theater_id'];
    $date = $_POST['show_date']; $time = $_POST['show_time']; $price = (float)$_POST['price'];
    if ($id) {
        $s = $conn->prepare("UPDATE shows SET movie_id=?,theater_id=?,show_date=?,show_time=?,price=? WHERE id=?");
        $s->bind_param('iissdi', $mid,$tid,$date,$time,$price,$id); $s->execute();
    } else {
        $s = $conn->prepare("INSERT INTO shows (movie_id,theater_id,show_date,show_time,price) VALUES (?,?,?,?,?)");
        $s->bind_param('iissd', $mid,$tid,$date,$time,$price); $s->execute();
    }
    flash('Saved'); header('Location: shows.php'); exit;
}
if (isset($_GET['delete'])) { $conn->query("DELETE FROM shows WHERE id=".(int)$_GET['delete']); flash('Deleted'); header('Location: shows.php'); exit; }
$edit = null;
if (isset($_GET['edit'])) {
    $s = $conn->prepare("SELECT * FROM shows WHERE id=?"); $s->bind_param('i',$_GET['edit']); $s->execute();
    $edit = $s->get_result()->fetch_assoc();
}
$movies = $conn->query("SELECT id,title FROM movies ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$theaters = $conn->query("SELECT id,name FROM theaters ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$shows = $conn->query("SELECT s.*, m.title, t.name AS tname FROM shows s JOIN movies m ON s.movie_id=m.id JOIN theaters t ON s.theater_id=t.id ORDER BY s.show_date DESC, s.show_time")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Shows'; require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div>
    <h2 class="section-title"><?= $edit?'Edit':'Add' ?> Show</h2>
    <form method="post" style="background:var(--surface); padding:20px; border-radius:10px; margin-bottom:30px;">
      <input type="hidden" name="id" value="<?= $edit['id']??'' ?>">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div class="form-group"><label>Movie</label><select name="movie_id" required>
          <?php foreach ($movies as $m): ?><option value="<?= $m['id'] ?>" <?= ($edit['movie_id']??0)==$m['id']?'selected':'' ?>><?= e($m['title']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Theater</label><select name="theater_id" required>
          <?php foreach ($theaters as $t): ?><option value="<?= $t['id'] ?>" <?= ($edit['theater_id']??0)==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Date</label><input type="date" name="show_date" required value="<?= e($edit['show_date']??'') ?>"></div>
        <div class="form-group"><label>Time</label><input type="time" name="show_time" required value="<?= e($edit['show_time']??'') ?>"></div>
        <div class="form-group"><label>Price (₹)</label><input type="number" step="0.01" name="price" required value="<?= e($edit['price']??200) ?>"></div>
      </div>
      <button class="btn">Save</button>
    </form>
    <div class="table-wrap"><table>
      <thead><tr><th>ID</th><th>Movie</th><th>Theater</th><th>Date</th><th>Time</th><th>Price</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($shows as $s): ?>
        <tr><td><?= $s['id'] ?></td><td><?= e($s['title']) ?></td><td><?= e($s['tname']) ?></td>
        <td><?= $s['show_date'] ?></td><td><?= date('g:i A', strtotime($s['show_time'])) ?></td><td>₹<?= number_format($s['price']) ?></td>
        <td><a href="?edit=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
        <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
