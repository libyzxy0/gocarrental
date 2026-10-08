<?php require '../config.php'; require_admin(); $title = 'Admin - Bookings';
$status = $_GET['status'] ?? ''; $q = trim($_GET['q'] ?? '');
$sql = 'SELECT b.*, u.full_name, u.email, c.name AS car_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN cars c ON c.id=b.car_id WHERE 1'; $p = [];
if (in_array($status, ['confirmed', 'completed', 'cancelled'], true)) { $sql .= ' AND b.status=?'; $p[] = $status; } else { $status = ''; }
if ($q !== '') { $sql .= ' AND (b.reference LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR c.name LIKE ?)'; array_push($p, "%$q%", "%$q%", "%$q%", "%$q%"); }
$s = db()->prepare($sql . ' ORDER BY b.id DESC LIMIT 200'); $s->execute($p); $rows = $s->fetchAll();
$counts = db()->query('SELECT status, COUNT(*) FROM bookings GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
include '../includes/header.php'; $adminPage = 'bookings'; include '../includes/admin_nav.php'; ?>
<div class="page-head"><h1>Bookings</h1>
  <form method="get" class="inline-search"><input type="hidden" name="status" value="<?= e($status) ?>"><input name="q" value="<?= e($q) ?>" placeholder="Search reference, name, car..."></form></div>
<nav class="pills">
  <a class="<?= $status === '' ? 'on' : '' ?>" href="bookings.php">All (<?= array_sum($counts) ?>)</a>
  <?php foreach (['confirmed', 'completed', 'cancelled'] as $st): ?>
    <a class="<?= $status === $st ? 'on' : '' ?>" href="bookings.php?status=<?= $st ?>"><?= ucfirst($st) ?> (<?= $counts[$st] ?? 0 ?>)</a><?php endforeach; ?>
</nav>
<table>
<tr><th>Reference</th><th>Customer</th><th>Car</th><th>Dates</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><b><?= e($r['reference']) ?></b></td>
  <td><?= e($r['full_name']) ?><br><span class="muted small"><?= e($r['email']) ?></span></td>
  <td><?= e($r['car_name']) ?></td>
  <td><?= e($r['pickup_date']) ?><br><span class="muted small">to <?= e($r['dropoff_date']) ?> (<?= $r['days'] ?>d)</span></td>
  <td><?= peso($r['total']) ?></td>
  <td><?= e($r['payment_method']) ?><br><span class="pill <?= e($r['payment_status']) ?>"><?= e($r['payment_status']) ?></span></td>
  <td><span class="pill <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
  <td><a class="btn small gray" href="booking_view.php?id=<?= $r['id'] ?>">View</a></td>
</tr>
<?php endforeach; ?>
</table>
<?php if (!$rows) echo '<div class="empty" style="margin-top:16px">No bookings found.</div>'; ?>
<?php include '../includes/footer.php'; ?>
