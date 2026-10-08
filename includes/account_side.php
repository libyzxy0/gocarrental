<?php $links = ['account' => ['account.php', 'Account Details'], 'bookings' => ['my_bookings.php', 'My Bookings'],
                'favorites' => ['favorites.php', 'Favorites'], 'reviews' => ['reviews.php', 'Reviews'],
                'settings' => ['settings.php', 'Settings']]; ?>
<aside class="side"><div class="avatar"><?= e(strtoupper(mb_substr($u['full_name'], 0, 1))) ?></div><b>Hi, <?= e($u['full_name']) ?></b><small><?= e($u['email']) ?></small>
  <?php foreach ($links as $k => $l): ?><a class="<?= ($active ?? '') === $k ? 'on' : '' ?>" href="<?= BASE_URL ?>/<?= $l[0] ?>"><?= $l[1] ?></a><?php endforeach; ?>
  <a href="<?= BASE_URL ?>/logout.php">Log Out</a>
</aside>