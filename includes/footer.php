</main>
<footer class="foot"><div class="wrap">
  <div class="foot-in">
    <div><a class="brand" href="<?= BASE_URL ?>/index.php"><span class="brand-mark"><?= car_svg() ?></span>Go Car Rental</a><p>More than just a rental. It's freedom.</p></div>
    <nav class="flinks"><a href="<?= BASE_URL ?>/index.php">Home</a><a href="<?= BASE_URL ?>/cars.php">Cars</a><a href="<?= BASE_URL ?>/about.php">About</a><a href="<?= BASE_URL ?>/contact.php">Contact</a></nav>
  </div>
  <div class="copy">© <?= date('Y') ?> Go Car Rental. All rights reserved.</div>
</div></footer>
<script>document.addEventListener('click',function(e){document.querySelectorAll('details.menu[open]').forEach(function(d){if(!d.contains(e.target))d.open=false;});});</script>
</body></html>
