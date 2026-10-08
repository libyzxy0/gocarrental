<nav class="pills">
  <a class="<?= ($adminPage ?? '') === 'cars' ? 'on' : '' ?>" href="<?= BASE_URL ?>/admin/index.php">Cars</a>
  <a class="<?= ($adminPage ?? '') === 'bookings' ? 'on' : '' ?>" href="<?= BASE_URL ?>/admin/bookings.php">Bookings</a>
</nav>
