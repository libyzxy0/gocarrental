<?php require 'config.php'; require_login(); $title = 'My Bookings'; $u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'cancel') {
        $s = db()->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status='confirmed' AND pickup_date >= CURDATE()");
        $s->execute([(int)($_POST['id'] ?? 0), $u['id']]);
        $ok = $s->rowCount() > 0;
        flash($ok ? 'Booking cancelled.' : 'This booking can no longer be cancelled.', $ok ? 'ok' : 'err');
    }
    redirect('/my_bookings.php');
}
$s = db()->prepare('SELECT b.*, c.name AS car_name, c.image, r.id AS review_id FROM bookings b JOIN cars c ON c.id=b.car_id
                    LEFT JOIN reviews r ON r.booking_id=b.id WHERE b.user_id=? ORDER BY b.id DESC');
$s->execute([$u['id']]); $rows = $s->fetchAll();
include 'includes/header.php'; ?>
<div class="side-layout">
  <?php $active = 'bookings'; include 'includes/account_side.php'; ?>
  <section>
    <div class="page-head"><h1>My Bookings</h1></div>
    <?php foreach ($rows as $r): ?>
      <div class="bk">
        <div class="tiny big2"><?= car_img(['image' => $r['image'], 'name' => $r['car_name']]) ?></div>
        <div style="flex:1">
          <b><?= e($r['car_name']) ?></b>
          <span class="pill <?= e($r['status']) ?>"><?= strtoupper(e($r['status'])) ?></span><br>
          Booked in: <?= e($r['pickup_date']) ?> – <?= e($r['dropoff_date']) ?> (<?= $r['days'] ?> days)<br>
          Confirmation number: <?= e($r['reference']) ?><br>
          Price: <?= peso($r['price_per_day']) ?> / day • Total <?= peso($r['total']) ?><br>
          Payment: <?= e($r['payment_method']) ?> (<?= e($r['payment_status']) ?>)
          <?php if ($r['status'] === 'completed' && !$r['review_id']): ?><br><a class="btn small" style="margin-top:8px" href="review.php?booking_id=<?= $r['id'] ?>">Write a review</a><?php endif; ?>
          <?php if ($r['status'] === 'confirmed' && $r['pickup_date'] >= date('Y-m-d')): ?>
            <form method="post" onsubmit="return confirm('Cancel this booking?')" style="margin-top:8px"><?= csrf_field() ?>
              <input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn small gray">Cancel booking</button></form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows) echo '<div class="empty">No bookings yet. <a href="cars.php">Browse cars</a></div>'; ?>
  </section>
</div>
<?php include 'includes/footer.php'; ?>
