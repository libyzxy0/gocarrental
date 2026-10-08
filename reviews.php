<?php require 'config.php'; require_login(); $title = 'Reviews'; $u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        db()->prepare('DELETE FROM reviews WHERE id=? AND user_id=?')->execute([(int)$_POST['id'], $u['id']]); flash('Review deleted.');
    }
    redirect('/reviews.php');
}
$s = db()->prepare('SELECT r.*, c.name AS car_name, c.image FROM reviews r JOIN cars c ON c.id=r.car_id WHERE r.user_id=? ORDER BY r.id DESC');
$s->execute([$u['id']]); $rows = $s->fetchAll();
include 'includes/header.php'; ?>
<div class="side-layout">
  <?php $active = 'reviews'; include 'includes/account_side.php'; ?>
  <section>
    <div class="page-head"><h1>Reviews</h1><a class="btn" href="review.php">Write a review</a></div>
    <?php foreach ($rows as $r): ?>
      <div class="bk">
        <div class="tiny big2"><?= car_img(['image' => $r['image'], 'name' => $r['car_name']]) ?></div>
        <div style="flex:1">
          <b><?= e($r['car_name']) ?></b> <?= stars($r['rating']) ?><?= $r['anonymous'] ? ' <span class="pill">Anonymous</span>' : '' ?><br>
          <?= $r['comment'] ? nl2br(e($r['comment'])) : '<span class="muted">No written feedback.</span>' ?><br>
          <span class="muted small"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
        </div>
        <form method="post" onsubmit="return confirm('Delete this review?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn small gray" style="margin:0">Delete</button></form>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows) echo '<div class="empty">You haven\'t written any reviews yet.</div>'; ?>
  </section>
</div>
<?php include 'includes/footer.php'; ?>
