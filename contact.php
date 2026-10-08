<?php require 'config.php'; $title = 'Contact'; $user = current_user();
$subjects = ['Booking inquiry', 'Payment', 'Cancellation / refund', 'Feedback', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!empty($_POST['website'])) redirect('/contact.php');          // honeypot: bots fill this hidden field
    $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $subject = $_POST['subject'] ?? ''; $msg = trim($_POST['message'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($subject, $subjects, true) || mb_strlen($msg) < 10) {
        $_SESSION['old'] = compact('name', 'email', 'subject', 'msg');
        flash('Please fill in every field. Your message needs at least 10 characters.', 'err'); redirect('/contact.php');
    }
    db()->prepare('INSERT INTO messages (user_id, full_name, email, subject, message) VALUES (?,?,?,?,?)')
        ->execute([$user['id'] ?? null, $name, $email, $subject, $msg]);
    flash("Message sent. We'll get back to you within 24 hours."); redirect('/contact.php');
}
$old = $_SESSION['old'] ?? []; unset($_SESSION['old']);
$v_name = $old['name'] ?? ($user['full_name'] ?? ''); $v_email = $old['email'] ?? ($user['email'] ?? '');
$v_subject = $old['subject'] ?? ''; $v_msg = $old['msg'] ?? '';
$address = '123 Unknown Street, Green City, Philippines';
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>We're here to help</h1>
  <p class="muted">Have questions, feedback, or need support? Get in touch with us. We'd love to hear from you.</p></div></div>

<div class="contact-grid">
  <div class="box wide"><h3 style="margin-top:0">Get in touch</h3>
    <div class="info">
      <div><?= icon('pin', 20) ?><p style="margin:0"><b>Our location</b><span><?= e($address) ?></span></p></div>
      <div><?= icon('phone', 20) ?><p style="margin:0"><b>Phone</b><span>+63 0906 574 2569<br>Mon – Sat, 8:00 AM – 6:00 PM</span></p></div>
      <div><?= icon('mail', 20) ?><p style="margin:0"><b>Email</b><span>support@gocarrental.com<br>We'll respond within 24 hours.</span></p></div>
      <div><?= icon('clock', 20) ?><p style="margin:0"><b>Business hours</b><span>Mon – Sat, 8:00 AM – 6:00 PM<br>Closed on Sundays</span></p></div>
    </div>
    <!-- TODO: social media links (Facebook, Instagram, X, YouTube) -->
  </div>

  <form class="box wide" method="post"><h3 style="margin-top:0">Send us a message</h3><?= csrf_field() ?>
    <p class="muted">Fill out the form below and we'll get back to you as soon as possible.</p>
    <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
    <label>Full name *<input name="full_name" value="<?= e($v_name) ?>" placeholder="Enter your full name" required></label>
    <label>Email address *<input type="email" name="email" value="<?= e($v_email) ?>" placeholder="example@gmail.com" required></label>
    <label>Subject *<select name="subject" required><option value="">Select a subject</option>
      <?php foreach ($subjects as $s): ?><option <?= $v_subject === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select></label>
    <label>Message *<textarea name="message" rows="6" placeholder="Type your message here..." required><?= e($v_msg) ?></textarea></label>
    <button class="btn">Send message</button>
  </form>
</div>

<div class="visit">
  <div><h2>Visit us</h2><p>We're located at <?= e($address) ?>. Feel free to stop by or give us a call!</p></div>
  <a class="btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($address) ?>"><?= icon('pin', 18) ?> Get directions</a>
</div>
<?php include 'includes/footer.php'; ?>