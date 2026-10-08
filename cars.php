<?php require 'config.php'; $title = 'Cars';
$cat = $_GET['category'] ?? ''; $loc = trim($_GET['location'] ?? '');
$sql = 'SELECT * FROM cars WHERE 1'; $p = [];
if (in_array($cat, CATEGORIES)) { $sql .= ' AND category=?'; $p[] = $cat; }
if ($loc !== '') { $sql .= ' AND location LIKE ?'; $p[] = "%$loc%"; }
// TODO: exclude cars already booked between ?pickup and ?dropoff
$s = db()->prepare($sql . ' ORDER BY id DESC'); $s->execute($p); $cars = $s->fetchAll();
include 'includes/header.php'; ?>
<div class="page-head"><div><h1><?= $cat ? e($cat) . ' cars' : 'All cars' ?></h1>
  <p class="muted"><?= count($cars) ?> car<?= count($cars) == 1 ? '' : 's' ?> available<?= $loc !== '' ? ' near ' . e($loc) : '' ?></p></div></div>
<nav class="pills"><a class="<?= $cat === '' ? 'on' : '' ?>" href="cars.php">All</a>
  <?php foreach (CATEGORIES as $c): ?><a class="<?= $cat === $c ? 'on' : '' ?>" href="cars.php?category=<?= $c ?>"><?= $c ?></a><?php endforeach; ?></nav>
<div class="grid"><?php foreach ($cars as $c) echo car_card($c); ?></div>
<?php if (!$cars) echo '<div class="empty">No cars match your search. <a href="cars.php">See all cars</a></div>'; ?>
<?php include 'includes/footer.php'; ?>
