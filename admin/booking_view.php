<?php require '../config.php'; require_admin();
$id = (int)($_GET['id'] ?? 0);
$s = db()->prepare('SELECT b.*, u.full_name, u.email, u.phone, u.address, c.name AS car_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN cars c ON c.id=b.car_id WHERE b.id=?');
$s->execute([$id]); $b = $s->fetch(); if (!$b) { http_response_code(404); exit('Booking not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $st = $_POST['status'] ?? ''; $ps = $_POST['payment_status'] ?? '';
    if (!in_array($st, ['confirmed', 'completed', 'cancelled'], true) || !in_array($ps, ['pending', 'paid'], true)) { flash('Invalid values.', 'err'); }
    elseif ($st === 'confirmed' && $b['status'] !== 'confirmed' && !car_available($b['car_id'], $b['pickup_date'], $b['dropoff_date'], $b['id'])) {
        flash('Cannot re-confirm: the car is booked by someone else for these dates.', 'err');
    } else {
        db()->prepare('UPDATE bookings SET status=?, payment_status=? WHERE id=?')->execute([$st, $ps, $id]); flash('Booking updated.');
    }
    redirect('/admin/booking_view.php?id=' . $id);
}
$title = 'Booking ' . $b['reference']; include '../includes/header.php'; $adminPage = 'bookings'; include '../includes/admin_nav.php'; ?>
<a class="back" href="bookings.php"><?= icon('back', 16) ?> Back to bookings</a>
<div class="page-head"><h1>Booking <?= e($b['reference']) ?></h1><span class="pill <?= e($b['status']) ?>"><?= strtoupper(e($b['status'])) ?></span></div>
<div class="two-col">
  <div>
    <div class="box wide"><h3 style="margin-top:0">Rental</h3>
      <dl class="kv">
        <dt>Car</dt><dd><?= e($b['car_name']) ?></dd>
        <dt>Pick-up</dt><dd><?= e($b['pickup_date']) ?> · <?= e($b['pickup_location']) ?></dd>
        <dt>Drop-off</dt><dd><?= e($b['dropoff_date']) ?> · <?= e($b['dropoff_location']) ?></dd>
        <dt>Duration</dt><dd><?= $b['days'] ?> day<?= $b['days'] > 1 ? 's' : '' ?> × <?= peso($b['price_per_day']) ?></dd>
        <dt>Total</dt><dd><b><?= peso($b['total']) ?></b></dd>
        <dt>Payment</dt><dd><?= e($b['payment_method']) ?> (<?= e($b['payment_status']) ?>)</dd>
        <dt>Booked on</dt><dd><?= e($b['created_at']) ?></dd>
      </dl></div>
    <div class="box wide"><h3 style="margin-top:0">Customer</h3>
      <dl class="kv">
        <dt>Name</dt><dd><?= e($b['full_name']) ?></dd><dt>Email</dt><dd><?= e($b['email']) ?></dd>
        <dt>Phone</dt><dd><?= e($b['phone'] ?: '—') ?></dd><dt>Address</dt><dd><?= e($b['address'] ?: '—') ?></dd>
      </dl></div>
  </div>
  <div>
    <div class="box wide"><h3 style="margin-top:0">Update booking</h3>
      <form method="post"><?= csrf_field() ?>
        <label>Status<select name="status"><?php foreach (['confirmed', 'completed', 'cancelled'] as $st): ?><option value="<?= $st ?>" <?= $b['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?></select></label>
        <label>Payment<select name="payment_status"><?php foreach (['pending', 'paid'] as $ps): ?><option value="<?= $ps ?>" <?= $b['payment_status'] === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option><?php endforeach; ?></select></label>
        <button class="btn">Save changes</button>
      </form>
      <p class="muted small" style="margin:14px 0 0">Mark a booking <b>completed</b> after the car is returned so the customer can write a review.</p></div>
    <div class="box wide"><h3 style="margin-top:0">Submitted ID: <?= e($b['id_type']) ?></h3>
      <a href="id_image.php?id=<?= $b['id'] ?>" target="_blank"><img class="idimg" src="id_image.php?id=<?= $b['id'] ?>" alt="Customer ID"></a></div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
