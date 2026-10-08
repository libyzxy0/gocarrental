<?php require 'config.php';
$s = db()->prepare('SELECT * FROM cars WHERE id=?'); $s->execute([(int)($_GET['id'] ?? 0)]); $c = $s->fetch();
if (!$c) { http_response_code(404); exit('Car not found'); }
$rs = db()->prepare('SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.car_id=? ORDER BY r.id DESC LIMIT 20');
$rs->execute([$c['id']]); $reviews = $rs->fetchAll(); [$avg, $rc] = car_rating($c['id']);
$title = $c['name']; include 'includes/header.php'; ?>
<a class="back" href="cars.php"><?= icon('back', 16) ?> Back to cars</a>
<div class="detail">
  <div>
    <div class="gallery"><?= car_img($c) ?></div>
    <h2>Description</h2>
    <p><?= nl2br(e($c['description'] ?: 'No description yet.')) ?></p>
    <h2 id="reviews">Reviews<?= $rc ? ' <small>(' . $rc . ')</small>' : '' ?></h2>
    <?php foreach ($reviews as $r): ?>
      <div class="review"><div class="rv-top"><b><?= $r['anonymous'] ? 'Anonymous' : e(reviewer_name($r['full_name'])) ?></b> <?= stars($r['rating']) ?>
        <span class="muted small"><?= date('M j, Y', strtotime($r['created_at'])) ?></span></div>
        <?php if ($r['comment']): ?><p><?= nl2br(e($r['comment'])) ?></p><?php endif; ?></div>
    <?php endforeach; ?>
    <?php if (!$reviews) echo '<p class="muted">No reviews yet. Reviews appear after a completed booking.</p>'; ?>
    <!-- TODO: gallery thumbnails, features list, availability calendar -->
  </div>
  <aside class="book-card">
    <div class="book-top"><span class="tag"><?= e($c['category']) ?></span><span class="fav-inline"><?= heart_btn($c['id']) ?></span></div>
    <h1><?= e($c['name']) ?></h1>
    <?php if ($rc): ?><div class="rating-line"><?= stars((int)round($avg)) ?> <b><?= $avg ?></b> <a href="#reviews">(<?= $rc ?> review<?= $rc > 1 ? 's' : '' ?>)</a></div><?php endif; ?>
    <div class="price"><?= peso($c['price_per_day']) ?><small>/day</small></div>
    <div class="chips">
      <span class="chip"><?= icon('users', 16) ?> <?= $c['seats'] ?> seats</span>
      <span class="chip"><?= icon('wheel', 16) ?> <?= e($c['transmission']) ?></span>
      <span class="chip"><?= icon('fuel', 16) ?> <?= e($c['fuel']) ?></span>
    </div>
    <?php if ($c['location']): ?><div class="loc"><?= icon('pin', 18) ?> <?= e($c['location']) ?></div><?php endif; ?>
    <a class="btn block" href="book.php?car_id=<?= $c['id'] ?>">Book now</a>
    <p class="muted small" style="margin:12px 0 0">You'll verify your ID and pick your dates in the next steps.</p>
  </aside>
</div>
<?php include 'includes/footer.php'; ?>
