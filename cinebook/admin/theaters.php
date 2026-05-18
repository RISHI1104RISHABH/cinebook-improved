<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']); $loc = trim($_POST['location']); $seats = (int)$_POST['total_seats'];
    if ($id) {
        $s = $conn->prepare("UPDATE theaters SET name=?,location=?,total_seats=? WHERE id=?");
        $s->bind_param('ssii', $name,$loc,$seats,$id); $s->execute();
    } else {
        $s = $conn->prepare("INSERT INTO theaters (name,location,total_seats) VALUES (?,?,?)");
        $s->bind_param('ssi', $name,$loc,$seats); $s->execute();
    }
    flash('Saved'); header('Location: theaters.php'); exit;
}
if (isset($_GET['delete'])) { $conn->query("DELETE FROM theaters WHERE id=".(int)$_GET['delete']); flash('Deleted'); header('Location: theaters.php'); exit; }
$edit = null;
if (isset($_GET['edit'])) {
    $s = $conn->prepare("SELECT * FROM theaters WHERE id=?"); $s->bind_param('i', $_GET['edit']); $s->execute();
    $edit = $s->get_result()->fetch_assoc();
}
$list = $conn->query("SELECT * FROM theaters ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Theaters'; require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div>
    <h2 class="section-title"><?= $edit?'Edit':'Add' ?> Theater</h2>
    <form method="post" style="background:var(--surface); padding:20px; border-radius:10px; margin-bottom:30px;">
      <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
      <div class="form-group"><label>Name</label><input name="name" required value="<?= e($edit['name']??'') ?>"></div>
      <div class="form-group"><label>Location</label><input name="location" value="<?= e($edit['location']??'') ?>"></div>
      <div class="form-group"><label>Total Seats</label><input name="total_seats" type="number" value="<?= e($edit['total_seats']??60) ?>"></div>
      <button class="btn">Save</button>
    </form>
    <div class="table-wrap"><table>
      <thead><tr><th>ID</th><th>Name</th><th>Location</th><th>Seats</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($list as $t): ?>
        <tr><td><?= $t['id'] ?></td><td><?= e($t['name']) ?></td><td><?= e($t['location']) ?></td><td><?= $t['total_seats'] ?></td>
        <td><a href="?edit=<?= $t['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
        <a href="?delete=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
