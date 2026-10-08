<?php require 'config.php'; $title = 'Go Car Rental'; $full = true;
$featured = db()->query('SELECT * FROM cars WHERE featured=1 ORDER BY id DESC LIMIT 4')->fetchAll();
$hero = $featured[0] ?? [];
$cats = ['Economy' => 'Affordable & reliable', 'Compact' => 'Great for the city', 'SUV' => 'More space, more comfort',
         'Van' => 'For your group trips', 'Luxury' => 'Premium experience', 'Sports' => 'Feel the thrill'];
$feats = [['shield', 'Safe & reliable', 'Well-maintained vehicles for your peace of mind'], ['headset', '24/7 support', "We're here whenever you need us"],
          ['tag', 'Best rates', 'Competitive prices, no hidden fees'], ['zap', 'Easy booking', 'Quick and hassle-free process']];
include 'includes/header.php'; ?>
<section class="hero"><div class="wrap hero-grid">
  <div>
    <p class="kicker">Rent • Cars • Explore</p>
    <h1>Your next journey<br>starts here</h1>
    <p class="lead">Choose from a wide selection of well-maintained vehicles and enjoy a smooth, hassle-free experience.</p>
  </div>
  <div class="hero-art"><?= car_img($hero) ?></div>
</div></section>

<div class="wrap search-wrap">
  <!-- TODO: filter by pickup/drop-off date availability on search -->
  <form class="search" action="cars.php" method="get">
    <label class="f"><?= icon('pin', 22) ?><span>Pick-up location</span><input name="location" placeholder="e.g. Makati"></label>
    <label class="f"><?= icon('cal', 22) ?><span>Pick-up date</span><input type="date" name="pickup" min="<?= date('Y-m-d') ?>"></label>
    <label class="f"><?= icon('cal', 22) ?><span>Drop-off date</span><input type="date" name="dropoff" min="<?= date('Y-m-d') ?>"></label>
    <label class="f"><?= icon('car', 22) ?><span>Car type</span><select name="category"><option value="">All types</option>
      <?php foreach (CATEGORIES as $c): ?><option><?= $c ?></option><?php endforeach; ?></select></label>
    <button class="btn">Search cars</button>
  </form>
</div>

<div class="wrap">
  <div class="sec-head"><div><h2>Browse by category</h2><p>Find the perfect car for your trip.</p></div></div>
  <div class="cats">
    <?php foreach ($cats as $name => $tag): ?>
      <a class="cat" href="cars.php?category=<?= $name ?>"><?= icon('car', 30) ?><b><?= $name ?></b><span><?= e($tag) ?></span><span class="go"><?= icon('arrow', 18) ?></span></a>
    <?php endforeach; ?>
  </div>

  <div class="sec-head"><div><h2>Featured cars</h2><p>Top picks for your next ride.</p></div><a href="cars.php">View all cars</a></div>
  <div class="grid"><?php foreach ($featured as $c) echo car_card($c); ?></div>
  <?php if (!$featured) echo '<div class="empty">No featured cars yet.</div>'; ?>
</div>

<section class="band"><div class="wrap band-in">
  <div><h2>More than just a rental.<br>It's freedom.</h2><p>Explore new places, create new memories, with the right car for every journey.</p></div>
  <div class="feats"><?php foreach ($feats as $f): ?>
    <div class="feat"><?= icon($f[0], 22) ?><h4><?= e($f[1]) ?></h4><p><?= e($f[2]) ?></p></div><?php endforeach; ?></div>
</div></section>
<?php include 'includes/footer.php'; ?>
