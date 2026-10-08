<?php require 'config.php'; require_login(); $title = 'Review your experience'; $u = current_user();
// bookings the user can review: completed and not reviewed yet
$s = db()->prepare("SELECT b.id, b.pickup_date, b.dropoff_date, b.car_id, c.name FROM bookings b JOIN cars c ON c.id=b.car_id
                    LEFT JOIN reviews r ON r.booking_id=b.id WHERE b.user_id=? AND b.status='completed' AND r.id IS NULL ORDER BY b.id DESC");
$s->execute([$u['id']]); $elig = $s->fetchAll();
$byId = array_column($elig, null, 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $bid = (int)($_POST['booking_id'] ?? 0); $rating = (int)($_POST['rating'] ?? 0);
    if (!isset($byId[$bid])) { flash('That booking cannot be reviewed.', 'err'); redirect('/review.php'); }
    if ($rating < 1 || $rating > 5) { flash('Please choose a star rating.', 'err'); redirect('/review.php?booking_id=' . $bid); }
    try {
        db()->prepare('INSERT INTO reviews (user_id,car_id,booking_id,rating,comment,anonymous) VALUES (?,?,?,?,?,?)')
            ->execute([$u['id'], $byId[$bid]['car_id'], $bid, $rating, trim($_POST['comment'] ?? ''), isset($_POST['anonymous']) ? 1 : 0]);
        flash('Thanks for your review!');
    } catch (PDOException $ex) { flash('You already reviewed this booking.', 'err'); }
    redirect('/reviews.php');
}
$sel = (int)($_GET['booking_id'] ?? 0);
include 'includes/header.php'; ?>
<?php if (!$elig): ?>
  <div class="empty">You can review a car once your booking is marked completed.<br><a href="my_bookings.php">Go to My Bookings</a></div>
<?php else: ?>
<form class="box" method="post"><h1>Review your experience</h1><?= csrf_field() ?>
  <label>Booking
    <select name="booking_id"><?php foreach ($elig as $b): ?>
      <option value="<?= $b['id'] ?>" <?= $sel == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?> (<?= e($b['pickup_date']) ?> – <?= e($b['dropoff_date']) ?>)</option><?php endforeach; ?></select></label>
  <label>How was your overall experience?</label>
  <div class="rate">
    <?php for ($i = 5; $i >= 1; $i--): ?><input type="radio" id="r<?= $i ?>" name="rating" value="<?= $i ?>" required><label for="r<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label><?php endfor; ?>
  </div>
  <label>Write your feedback (optional)<textarea name="comment" rows="5" placeholder="Tell us about your experience..."></textarea></label>
  <label class="chk"><input type="checkbox" name="anonymous" value="1"> Submit anonymously</label>
  <button class="btn">Submit review</button> <a class="btn gray" href="reviews.php">Skip</a>
</form>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
