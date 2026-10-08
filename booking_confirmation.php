<?php require 'config.php'; require_login(); $title = 'Booking Confirmed';
$s = db()->prepare('SELECT b.*, c.name AS car_name, c.image, c.seats, c.transmission FROM bookings b JOIN cars c ON c.id=b.car_id WHERE b.reference=? AND b.user_id=?');
$s->execute([$_GET['ref'] ?? '', $_SESSION['user_id']]); $b = $s->fetch();
if (!$b) { flash('Booking not found.', 'err'); redirect('/my_bookings.php'); }
include 'includes/header.php'; ?>
<div class="box wide center">
  <div class="okmark"><?= icon('check', 38) ?></div>
  <h1>Booking Confirmed!</h1>
  <p><?= $b['payment_status'] === 'paid' ? 'Payment successful.' : 'Payment is pending (' . e($b['payment_method']) . ').' ?> Keep your booking reference below.</p>
  <!-- TODO: send confirmation email -->
  <div class="note left">
    <b><?= e($b['car_name']) ?></b><br>
    <?= e($b['pickup_date']) ?> → <?= e($b['dropoff_date']) ?> (<?= $b['days'] ?> day<?= $b['days'] > 1 ? 's' : '' ?>)<br>
    Pick-Up: <?= e($b['pickup_location']) ?><br>Drop-Off: <?= e($b['dropoff_location']) ?><br>
    Booking reference: <b>#<?= e($b['reference']) ?></b><br>
    Total: <b><?= peso($b['total']) ?></b>
  </div>
  <a class="btn" href="my_bookings.php">View my bookings</a> <a class="btn gray" href="index.php">Back to home</a>
</div>
<?php include 'includes/footer.php'; ?>
