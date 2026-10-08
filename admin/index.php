<?php require '../config.php'; require_admin(); $title = 'Admin - Cars';
$cars = db()->query('SELECT * FROM cars ORDER BY id DESC')->fetchAll();
include '../includes/header.php'; ?>
<?php $adminPage = 'cars'; include '../includes/admin_nav.php'; ?>
<div class="page-head"><h1>Manage cars</h1><a class="btn" href="car_form.php">Add car</a></div>
<!-- TODO: admin pages for bookings, users, reviews, contact messages -->
<table>
<tr><th>Image</th><th>Name</th><th>Category</th><th>Price/day</th><th>Seats</th><th>Featured</th><th></th></tr>
<?php foreach ($cars as $c): ?>
<tr>
  <td><div class="tiny"><?= car_img($c) ?></div></td>
  <td><?= e($c['name']) ?></td><td><?= e($c['category']) ?></td><td><?= peso($c['price_per_day']) ?></td>
  <td><?= $c['seats'] ?></td><td><?= $c['featured'] ? '★' : '' ?></td>
  <td>
    <a class="btn small" href="car_form.php?id=<?= $c['id'] ?>">Edit</a>
    <form method="post" action="car_delete.php" style="display:inline" onsubmit="return confirm('Delete this car?')">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn small danger">Delete</button></form>
  </td>
</tr>
<?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>
