<?php require 'config.php'; require_login(); $title = 'Settings'; $u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? ''); $phone = trim($_POST['phone'] ?? '');
    $notif = isset($_POST['email_notifications']) ? 1 : 0;
    $theme = ($_POST['theme'] ?? '') === 'dark' ? 'dark' : 'light';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('Enter a valid name and email.', 'err'); redirect('/settings.php'); }
    $s = db()->prepare('SELECT id FROM users WHERE email=? AND id<>?'); $s->execute([$email, $u['id']]);
    if ($s->fetch()) { flash('That email is already used by another account.', 'err'); redirect('/settings.php'); }
    db()->prepare('UPDATE users SET full_name=?, email=?, phone=?, email_notifications=?, theme=? WHERE id=?')
        ->execute([$name, $email, $phone, $notif, $theme, $u['id']]);
    flash('Settings saved.'); redirect('/settings.php');
}
include 'includes/header.php'; ?>
<div class="side-layout">
  <?php $active = 'settings'; include 'includes/account_side.php'; ?>
  <section>
    <div class="page-head"><h1>Settings</h1></div>
    <form method="post"><?= csrf_field() ?>

      <div class="box wide"><h3 style="margin-top:0">Account settings</h3>
        <p class="muted">Manage your personal information.</p>
        <div class="two">
          <label>Name<input name="full_name" value="<?= e($u['full_name']) ?>" required></label>
          <label>Phone<input name="phone" value="<?= e($u['phone']) ?>"></label>
        </div>
        <label>Email<input type="email" name="email" value="<?= e($u['email']) ?>" required></label>
        <p class="small muted" style="margin:12px 0 0">To change your password, go to <a href="account.php">Account Details</a>.</p>
      </div>

      <div class="box wide"><h3 style="margin-top:0">Notifications</h3>
        <p class="muted">Choose what you want to be notified about.</p>
        <div class="setrow">
          <div><b>Email notifications</b><br><span class="muted small">Receive updates about your bookings, offers and promotions.</span></div>
          <label class="switch"><input type="checkbox" name="email_notifications" value="1" <?= $u['email_notifications'] ? 'checked' : '' ?>><span></span></label>
        </div>
        <!-- TODO: actually send emails (PHPMailer/SMTP) when this is on -->
      </div>

      <div class="box wide"><h3 style="margin-top:0">Appearance</h3>
        <p class="muted">Customize how the website looks.</p>
        <label class="pay"><input type="radio" name="theme" value="light" <?= $u['theme'] !== 'dark' ? 'checked' : '' ?>> Light mode</label>
        <label class="pay"><input type="radio" name="theme" value="dark" <?= $u['theme'] === 'dark' ? 'checked' : '' ?>> Dark mode</label>
      </div>

      <button class="btn">Save settings</button>
    </form>
  </section>
</div>
<?php include 'includes/footer.php'; ?>