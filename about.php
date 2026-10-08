<?php require 'config.php'; $title = 'About'; $full = true;
$points = [['car', 'Reliable vehicles', 'Well-maintained and regularly checked.'],
           ['tag', 'Affordable rates', 'Quality service at the best prices.'],
           ['headset', '24/7 support', "We're always here to help you."]];
$why = [['shield', 'Safe & secure', 'Your safety is our priority. All vehicles are inspected before every rental.'],
        ['zap', 'Easy booking', 'Book in just a few clicks and get on the road in no time.'],
        ['car', 'Wide selection', 'Choose from a variety of cars for every kind of adventure.'],
        ['headset', 'Customer support', "We're always ready 24/7 to assist you whenever you need help."]];
include 'includes/header.php'; ?>
<section class="hero"><div class="wrap hero-grid">
  <div>
    <p class="kicker">About us</p>
    <h1>More than just<br>a rental</h1>
    <p class="lead">At Go Car Rental, we make your journey easier, safer, and more convenient. We're not just a car rental service, we're your travel partner.</p>
  </div>
  <div class="hero-art"><?= car_img([]) ?></div>
</div></section>

<div class="wrap">
  <div class="about-grid" style="margin-top:56px">
    <div>
      <h2>Our story</h2>
      <p>Go Car Rental was created with a simple goal: to give everyone the freedom to go whenever they want. We noticed that travelling should be exciting, not stressful, so we built a platform that makes car rentals easy, reliable, and affordable.</p>
      <p>From daily commutes to weekend getaways, Go Car Rental is here to support every journey, big or small.</p>
    </div>
    <div class="points">
      <?php foreach ($points as $p): ?>
        <div class="point"><?= icon($p[0], 22) ?><div><b><?= e($p[1]) ?></b><span><?= e($p[2]) ?></span></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<section class="band"><div class="wrap">
  <h2>Why choose Go Car Rental?</h2>
  <p class="band-sub">We go the extra mile to make your rental experience smooth and hassle-free.</p>
  <div class="feats" style="margin-top:28px">
    <?php foreach ($why as $f): ?>
      <div class="feat"><?= icon($f[0], 22) ?><h4><?= e($f[1]) ?></h4><p><?= e($f[2]) ?></p></div>
    <?php endforeach; ?>
  </div>
</div></section>
<?php include 'includes/footer.php'; ?>