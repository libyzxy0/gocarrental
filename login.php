<?php require 'config.php'; $title = 'Log In';
if (is_logged_in()) redirect('/index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $s = db()->prepare('SELECT * FROM users WHERE email=?'); $s->execute([trim($_POST['email'] ?? '')]); $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true); $_SESSION['user_id'] = $u['id'];
        $next = $_SESSION['after_login'] ?? null; unset($_SESSION['after_login']);
        if ($next && strpos($next, BASE_URL . '/') === 0 && strpos($next, '//') === false) { header('Location: ' . $next); exit; }
        redirect($u['role'] === 'admin' ? '/admin/index.php' : '/index.php');
    }
    flash('Invalid email or password.', 'err'); redirect('/login.php');
}
include 'includes/header.php'; ?>
<form class="box auth" method="post"><h1>Welcome back</h1><p class="muted" style="text-align:left;margin:0">Log in to manage your bookings.</p><?= csrf_field() ?>
  <label>Email Address<input type="email" name="email" required></label>
  <label>Password<input type="password" name="password" required></label>
  <button class="btn">Log In</button>
  <p>Don't have an account? <a href="register.php">Sign Up</a></p>
  <!-- TODO: Continue with Google, forgot password -->
</form>
<?php include 'includes/footer.php'; ?>
