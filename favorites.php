<?php require 'config.php'; require_login(); $title = 'Favorites'; $u = current_user();
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT c.* FROM favorites f JOIN cars c ON c.id=f.car_id WHERE f.user_id=?'; $p = [$u['id']];
if ($q !== '') { $sql .= ' AND (c.name LIKE ? OR c.category LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
$s = db()->prepare($sql . ' ORDER BY f.created_at DESC'); $s->execute($p); $cars = $s->fetchAll();
include 'includes/header.php'; ?>
<div class="side-layout">
  <?php $active = 'favorites'; include 'includes/account_side.php'; ?>
  <section>
    <div class="page-head"><h1>Favorites</h1>
      <form method="get" class="inline-search"><input name="q" value="<?= e($q) ?>" placeholder="Search favorites..."></form></div>
    <div class="grid"><?php foreach ($cars as $c) echo car_card($c); ?></div>
    <?php if (!$cars) echo '<div class="empty">' . ($q !== '' ? 'No favorites match your search.' : 'No favorites yet. Tap the heart on a car to save it here.') . ' <a href="cars.php">Browse cars</a></div>'; ?>
  </section>
</div>
<?php include 'includes/footer.php'; ?>
