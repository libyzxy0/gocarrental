<?php require 'config.php'; require_login(); $title = 'Account Details'; $u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'profile') {
        $name = trim($_POST['full_name']); $email = trim($_POST['email']);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('Invalid name or email.', 'err'); redirect('/account.php'); }
        $s = db()->prepare('SELECT id FROM users WHERE email=? AND id<>?'); $s->execute([$email, $u['id']]);
        if ($s->fetch()) { flash('Email already used.', 'err'); redirect('/account.php'); }
        db()->prepare('UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?')
            ->execute([$name, $email, trim($_POST['phone']), trim($_POST['address']), $u['id']]);
        flash('Profile updated.');
    } elseif ($_POST['action'] === 'password') {
        if (!password_verify($_POST['current'] ?? '', $u['password'])) flash('Current password is wrong.', 'err');
        elseif (strlen($_POST['new'] ?? '') < 6) flash('New password must be at least 6 characters.', 'err');
        else { db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); flash('Password changed.'); }
    }
    redirect('/account.php');
}
include 'includes/header.php'; ?>
<div class="side-layout">
  <?php $active = 'account'; include 'includes/account_side.php'; ?>
  <section>
    <h1>Account Details</h1>
    <form class="box" method="post"><h3>Personal Information</h3><?= csrf_field() ?><input type="hidden" name="action" value="profile">
      <label>Full Name<input name="full_name" value="<?= e($u['full_name']) ?>" required></label>
      <label>Email Address<input type="email" name="email" value="<?= e($u['email']) ?>" required></label>
      <label>Phone Number<input name="phone" value="<?= e($u['phone']) ?>"></label>
      <label>Address<input name="address" value="<?= e($u['address']) ?>"></label>
      <button class="btn">Save Profile</button>
    </form>
    <form class="box" method="post"><h3>Change Password</h3><?= csrf_field() ?><input type="hidden" name="action" value="password">
      <label>Current Password<input type="password" name="current" required></label>
      <label>New Password<input type="password" name="new" minlength="6" required></label>
      <button class="btn">Change Password</button>
    </form>
  </section>
</div>
<?php include 'includes/footer.php'; ?>
