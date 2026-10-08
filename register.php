<?php require 'config.php'; $title = 'Sign Up';
if (is_logged_in()) redirect('/index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? ''); $pw = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
        flash('Fill all fields. Password must be at least 6 characters.', 'err'); redirect('/register.php');
    }
    $s = db()->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$email]);
    if ($s->fetch()) { flash('Email already registered.', 'err'); redirect('/register.php'); }
    db()->prepare('INSERT INTO users (full_name,email,phone,password) VALUES (?,?,?,?)')
        ->execute([$name, $email, $phone, password_hash($pw, PASSWORD_DEFAULT)]);
    $_SESSION['user_id'] = db()->lastInsertId(); session_regenerate_id(true);
    flash('Welcome! Account created.');
    $next = $_SESSION['after_login'] ?? null; unset($_SESSION['after_login']);
    if ($next && strpos($next, BASE_URL . '/') === 0 && strpos($next, '//') === false) { header('Location: ' . $next); exit; }
    redirect('/index.php');
}
include 'includes/header.php'; ?>
<form class="box auth" method="post"><h1>Create an account</h1><p class="muted" style="text-align:left;margin:0">Join Go Car Rental to book your next trip.</p><?= csrf_field() ?>
  <label>Full Name<input name="full_name" required></label>
  <label>Email Address<input type="email" name="email" required></label>
  <label>Phone Number<input name="phone"></label>
  <label>Password<input type="password" name="password" minlength="6" required></label>
  <!-- TODO: driver's license / valid ID upload + age verification (3-step signup), Terms checkbox -->
  <button class="btn">Sign Up</button>
  <p>Already have an account? <a href="login.php">Login</a></p>
</form>
<?php include 'includes/footer.php'; ?>
