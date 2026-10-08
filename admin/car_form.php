<?php require '../config.php'; require_admin();
$id = (int)($_GET['id'] ?? 0); $car = null;
if ($id) { $s = db()->prepare('SELECT * FROM cars WHERE id=?'); $s->execute([$id]); $car = $s->fetch(); if (!$car) exit('Not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $f = [trim($_POST['name']), $_POST['category'], (float)$_POST['price_per_day'], trim($_POST['transmission']),
              (int)$_POST['seats'], trim($_POST['fuel']), trim($_POST['description']), trim($_POST['location']),
              trim($_POST['badge']) ?: null, isset($_POST['featured']) ? 1 : 0];
        if ($f[0] === '' || !in_array($f[1], CATEGORIES) || $f[2] <= 0 || $f[4] < 1) throw new Exception('Please complete all required fields.');
        $img = upload_image($_FILES['image'] ?? []);
        if ($car) {
            if ($img) { delete_image($car['image']); } else { $img = $car['image']; }
            db()->prepare('UPDATE cars SET name=?,category=?,price_per_day=?,transmission=?,seats=?,fuel=?,description=?,location=?,badge=?,featured=?,image=? WHERE id=?')
                ->execute([...$f, $img, $id]);
            flash('Car updated.');
        } else {
            db()->prepare('INSERT INTO cars (name,category,price_per_day,transmission,seats,fuel,description,location,badge,featured,image) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([...$f, $img]);
            flash('Car added.');
        }
        redirect('/admin/index.php');
    } catch (Exception $ex) { flash($ex->getMessage(), 'err'); redirect('/admin/car_form.php' . ($id ? "?id=$id" : '')); }
}
$v = fn($k, $d = '') => e($car[$k] ?? $d);
$title = $car ? 'Edit Car' : 'Add Car'; include '../includes/header.php'; ?>
<a class="back" href="index.php"><?= icon('back', 16) ?> Back to cars</a>
<form class="box" method="post" enctype="multipart/form-data"><h1><?= $title ?></h1><?= csrf_field() ?>
  <label>Name *<input name="name" value="<?= $v('name') ?>" required></label>
  <label>Category *<select name="category"><?php foreach (CATEGORIES as $c): ?>
    <option <?= ($car['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></label>
  <label>Price per day (₱) *<input type="number" step="0.01" min="1" name="price_per_day" value="<?= $v('price_per_day') ?>" required></label>
  <label>Transmission<input name="transmission" value="<?= $v('transmission', 'Automatic') ?>"></label>
  <label>Seats *<input type="number" min="1" name="seats" value="<?= $v('seats', 5) ?>" required></label>
  <label>Fuel<input name="fuel" value="<?= $v('fuel', 'Gasoline') ?>"></label>
  <label>Current location<input name="location" value="<?= $v('location') ?>"></label>
  <label>Badge (e.g. Best Seller, Popular)<input name="badge" value="<?= $v('badge') ?>"></label>
  <label>Description<textarea name="description" rows="5"><?= $v('description') ?></textarea></label>
  <label>Image (JPG/PNG/WEBP, max 5MB)<input type="file" name="image" accept="image/*"></label>
  <?php if (!empty($car['image'])): ?><div class="tiny"><?= car_img($car) ?></div><?php endif; ?>
  <label class="chk"><input type="checkbox" name="featured" <?= !empty($car['featured']) ? 'checked' : '' ?>> Show in Featured Cars</label>
  <!-- TODO: multiple gallery images + features list (A/C, Bluetooth, ...) -->
  <button class="btn">Save</button>
</form>
<?php include '../includes/footer.php'; ?>
