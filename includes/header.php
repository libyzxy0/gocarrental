<?php $user = current_user(); $page = basename($_SERVER['SCRIPT_NAME'], '.php'); $full = $full ?? false; ?>
<!DOCTYPE html><html lang="en" data-theme="<?= e($user['theme'] ?? 'light') ?>"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Go Car Rental') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css"></head><body>
<header class="nav"><div class="wrap nav-in">
  <a class="brand" href="<?= BASE_URL ?>/index.php"><span class="brand-mark"><?= car_svg() ?></span>Go Car Rental</a>
  <button class="burger" type="button" aria-label="Menu" onclick="document.body.classList.toggle('navopen')"><?= icon('menu', 26) ?></button>
  <nav class="links">
    <a class="<?= $page=='index'?'active':'' ?>" href="<?= BASE_URL ?>/index.php">Home</a>
    <a class="<?= in_array($page,['cars','car','book'])?'active':'' ?>" href="<?= BASE_URL ?>/cars.php">Cars</a>
    <a class="<?= $page=='about'?'active':'' ?>" href="<?= BASE_URL ?>/about.php">About</a>
    <a class="<?= $page=='contact'?'active':'' ?>" href="<?= BASE_URL ?>/contact.php">Contact</a>
  </nav>
  <div class="user">
    <?php if ($user): ?>
      <details class="menu"><summary><span class="av"><?= e(strtoupper(mb_substr($user['full_name'], 0, 1))) ?></span>Hi, <?= e(explode(' ', $user['full_name'])[0]) ?> <?= icon('chev', 16) ?></summary>
        <div class="drop">
          <a href="<?= BASE_URL ?>/account.php">Account details</a>
          <a href="<?= BASE_URL ?>/my_bookings.php">My bookings</a>
          <?php if ($user['role']==='admin'): ?><a href="<?= BASE_URL ?>/admin/index.php">Admin panel</a><?php endif; ?>
          <hr><a href="<?= BASE_URL ?>/logout.php">Log out</a>
        </div></details>
    <?php else: ?>
      <a class="btn gray" href="<?= BASE_URL ?>/login.php">Log in</a><a class="btn" href="<?= BASE_URL ?>/register.php">Sign up</a>
    <?php endif; ?>
  </div>
</div></header>
<?php if ($full): ?><main><div class="wrap"><?= flash() ?></div>
<?php else: ?><main class="wrap page"><?= flash() ?>
<?php endif; ?>
