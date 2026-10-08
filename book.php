<?php require 'config.php'; require_login();

$ID_TYPES = ["Driver's Licence", 'Passport', 'PhilSys/National ID', 'SSS ID', 'UMID', 'PhilHealth ID', 'Postal ID', "Voter's ID", 'Other Valid Government ID'];
$PAY      = ['Visa', 'Gcash', 'Bank transfer', 'Paymaya', 'Cash on Pick-Up'];
$ORDER    = ['terms', 'id', 'details', 'payment'];
$LABELS   = ['Terms', 'ID Verification', 'Booking Details', 'Payment'];
$PLACES   = ['Clark International Airport', 'Angeles City', 'Makati', 'Quezon City', 'NAIA Terminal 3'];

// start a new booking
if (isset($_GET['car_id'])) {
    $s = db()->prepare('SELECT id FROM cars WHERE id=?'); $s->execute([(int)$_GET['car_id']]);
    if (!$s->fetch()) { flash('Car not found.', 'err'); redirect('/cars.php'); }
    if (!empty($_SESSION['booking']['id_file'])) delete_private($_SESSION['booking']['id_file']);
    $_SESSION['booking'] = ['car_id' => (int)$_GET['car_id'], 'reached' => 0];
    redirect('/book.php');
}
if (empty($_SESSION['booking'])) redirect('/cars.php');
$b = $_SESSION['booking'];
$s = db()->prepare('SELECT * FROM cars WHERE id=?'); $s->execute([$b['car_id']]); $car = $s->fetch();
if (!$car) { unset($_SESSION['booking']); redirect('/cars.php'); }

$step = $_GET['step'] ?? $ORDER[$b['reached']];
$idx  = array_search($step, $ORDER, true);
if ($idx === false || $idx > $b['reached']) { $idx = $b['reached']; $step = $ORDER[$idx]; }
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $act = $_POST['action'] ?? '';
    try {
        if ($act === 'terms') {
            if (empty($_POST['agree'])) throw new Exception('You must agree to the Terms & Conditions.');
            $_SESSION['booking']['reached'] = max($b['reached'], 1);
            redirect('/book.php?step=id');
        }
        elseif ($act === 'id') {
            $type = $_POST['id_type'] ?? '';
            if (!in_array($type, $ID_TYPES, true)) throw new Exception('Select an ID type.');
            $file = upload_private_id($_FILES['id_file'] ?? []);
            if ($file) { delete_private($b['id_file'] ?? null); $_SESSION['booking']['id_file'] = $file; }
            elseif (empty($b['id_file'])) throw new Exception('Please upload a photo of your valid ID.');
            $_SESSION['booking']['id_type'] = $type;
            $_SESSION['booking']['reached'] = max($b['reached'], 2);
            redirect('/book.php?step=details');
        }
        elseif ($act === 'details') {
            $from = $_POST['pickup_date'] ?? ''; $to = $_POST['dropoff_date'] ?? '';
            $pl = trim($_POST['pickup_location'] ?? ''); $dl = trim($_POST['dropoff_location'] ?? '');
            if (!valid_date($from) || !valid_date($to)) throw new Exception('Please choose pick-up and drop-off dates.');
            if ($from < date('Y-m-d')) throw new Exception('Pick-up date cannot be in the past.');
            if ($to < $from) throw new Exception('Drop-off date must be on or after the pick-up date.');
            if ($pl === '' || $dl === '') throw new Exception('Please enter pick-up and drop-off locations.');
            if (!car_available($car['id'], $from, $to)) throw new Exception('Sorry, this car is already booked for those dates.');
            $_SESSION['booking'] = array_merge($_SESSION['booking'], ['pickup_date' => $from, 'dropoff_date' => $to,
                'pickup_location' => $pl, 'dropoff_location' => $dl, 'reached' => max($b['reached'], 3)]);
            redirect('/book.php?step=payment');
        }
        elseif ($act === 'payment') {
            $m = $_POST['payment_method'] ?? '';
            if (!in_array($m, $PAY, true)) throw new Exception('Choose a payment method.');
            if (empty($b['pickup_date']) || empty($b['id_file'])) throw new Exception('Please complete the previous steps.');
            if ($m === 'Visa') {   // card data is validated only, NEVER stored
                $num = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
                $exp = trim($_POST['expiry'] ?? '');
                if (!preg_match('/^\d{13,19}$/', $num)) throw new Exception('Invalid card number.');
                if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $exp, $mm)) throw new Exception('Expiry must be MM/YY.');
                if ((int)('20' . $mm[2]) * 100 + (int)$mm[1] < (int)date('Ym')) throw new Exception('Card has expired.');
                if (!preg_match('/^\d{3,4}$/', trim($_POST['cvv'] ?? ''))) throw new Exception('Invalid CVV.');
                if (trim($_POST['holder'] ?? '') === '') throw new Exception('Enter the card holder name.');
            }
            $days = rental_days($b['pickup_date'], $b['dropoff_date']);
            $total = $days * $car['price_per_day'];
            $pdo = db(); $pdo->beginTransaction();
            $pdo->prepare('SELECT id FROM cars WHERE id=? FOR UPDATE')->execute([$car['id']]);   // avoid double booking
            if (!car_available($car['id'], $b['pickup_date'], $b['dropoff_date'])) { $pdo->rollBack(); throw new Exception('Sorry, someone just booked this car for those dates.'); }
            $ref = strtoupper(bin2hex(random_bytes(6)));
            $pdo->prepare('INSERT INTO bookings (reference,user_id,car_id,pickup_date,dropoff_date,pickup_location,dropoff_location,days,price_per_day,total,id_type,id_file,payment_method,payment_status)
                           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$ref, $user['id'], $car['id'], $b['pickup_date'], $b['dropoff_date'], $b['pickup_location'], $b['dropoff_location'],
                           $days, $car['price_per_day'], $total, $b['id_type'], $b['id_file'], $m,
                           $m === 'Visa' ? 'paid' : 'pending']);   // TODO: real payment gateway (Visa is simulated)
            $pdo->commit();
            unset($_SESSION['booking']);
            redirect('/booking_confirmation.php?ref=' . $ref);
        }
    } catch (Exception $ex) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        flash($ex instanceof PDOException ? 'Something went wrong. Please try again.' : $ex->getMessage(), 'err');
        redirect('/book.php?step=' . $step);
    }
}

$days  = !empty($b['pickup_date']) ? rental_days($b['pickup_date'], $b['dropoff_date']) : null;
$total = $days ? $days * $car['price_per_day'] : null;
$title = 'Book ' . $car['name']; include 'includes/header.php'; ?>

<div class="steps">
  <?php foreach ($LABELS as $i => $l): ?><span class="<?= $i == $idx ? 'cur' : ($i < $idx ? 'done' : '') ?>"><i><?= $i < $idx ? '✓' : $i + 1 ?></i><?= $l ?></span><?php endforeach; ?>
  <span><i>5</i>Confirmation</span>
</div>

<div class="book-layout">
<section>
<?php if ($step === 'terms'): ?>
  <form class="box wide" method="post"><h2>Booking Terms and Conditions</h2><?= csrf_field() ?><input type="hidden" name="action" value="terms">
    <p>Before continuing with your booking, please read and understand the following terms and conditions.</p>
    <ul>
      <li>The renter must provide valid and accurate personal information.</li>
      <li>A valid identification document must be submitted before the booking can be confirmed.</li>
      <li>The rented vehicle must be returned on the agreed date and time.</li>
      <li>The renter is responsible for the vehicle during the rental period.</li>
      <li>Booking availability is subject to the selected vehicle and date.</li>
      <li>Any additional charges will be communicated before confirmation.</li>
      <li>By continuing, you confirm that you have read and agreed to these Terms &amp; Conditions.</li>
    </ul>
    <label class="chk"><input type="checkbox" name="agree" value="1"> I have read and agree to the Terms &amp; Conditions.</label>
    <a class="btn gray" href="car.php?id=<?= $car['id'] ?>">Back</a> <button class="btn">Agree and continue</button>
  </form>

<?php elseif ($step === 'id'): ?>
  <form class="box wide" method="post" enctype="multipart/form-data"><h2>Identity Verification</h2><?= csrf_field() ?><input type="hidden" name="action" value="id">
    <p class="note">ⓘ ID verification is required before proceeding with your booking.</p>
    <label>Select valid ID<select name="id_type">
      <?php foreach ($ID_TYPES as $t): ?><option <?= ($b['id_type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
    <label>Upload valid ID (JPG/PNG, max 5MB)<input type="file" name="id_file" accept="image/jpeg,image/png"></label>
    <?php if (!empty($b['id_file'])): ?><p>✓ ID already uploaded (choose a file only if you want to replace it).</p><?php endif; ?>
    <a class="btn gray" href="book.php?step=terms">Back</a> <button class="btn">Continue</button>
  </form>

<?php elseif ($step === 'details'): ?>
  <form class="box wide" method="post"><h2>Booking Details</h2><?= csrf_field() ?><input type="hidden" name="action" value="details">
    <div class="renter"><b>Renter Information</b> <a href="account.php">edit</a><br>
      <?= e($user['full_name']) ?><br><?= e($user['phone'] ?: 'No phone on file') ?><br><?= e($user['email']) ?></div>
    <div class="two">
      <label>Pick-Up Date<input type="date" id="pd" name="pickup_date" min="<?= date('Y-m-d') ?>" value="<?= e($b['pickup_date'] ?? '') ?>" required></label>
      <label>Drop-Off Date<input type="date" id="dd" name="dropoff_date" min="<?= date('Y-m-d') ?>" value="<?= e($b['dropoff_date'] ?? '') ?>" required></label>
      <label>Pick-Up Location<input list="places" name="pickup_location" value="<?= e($b['pickup_location'] ?? $car['location']) ?>" required></label>
      <label>Drop-Off Location<input list="places" name="dropoff_location" value="<?= e($b['dropoff_location'] ?? $car['location']) ?>" required></label>
    </div>
    <datalist id="places"><?php foreach ($PLACES as $p): ?><option value="<?= e($p) ?>"><?php endforeach; ?></datalist>
    <p><b>Rental Duration:</b> <span id="dur">—</span></p>
    <a class="btn gray" href="book.php?step=id">Back</a> <button class="btn">Next: Payment →</button>
  </form>
  <script>
  const f=document.getElementById('pd'),t=document.getElementById('dd'),o=document.getElementById('dur'),price=<?= (float)$car['price_per_day'] ?>;
  function upd(){ if(!f.value||!t.value){o.textContent='—';return;} t.min=f.value;
    const d=Math.max(1,Math.round((new Date(t.value)-new Date(f.value))/864e5)); o.textContent=d+' day(s) — ₱'+(d*price).toLocaleString(); }
  f.onchange=t.onchange=upd; upd();
  </script>

<?php elseif ($step === 'payment'): ?>
  <form class="box wide" method="post"><h2>Complete Your Payment</h2><?= csrf_field() ?><input type="hidden" name="action" value="payment">
    <h3>1. Booking summary</h3>
    <div class="note"><b><?= e($car['name']) ?></b><br>
      <?= e($b['pickup_date']) ?> → <?= e($b['dropoff_date']) ?> (<?= $days ?> day<?= $days > 1 ? 's' : '' ?>)<br>
      Pick-Up: <?= e($b['pickup_location']) ?><br>Drop-Off: <?= e($b['dropoff_location']) ?><br>
      <?= peso($car['price_per_day']) ?> / day &nbsp;•&nbsp; <b>Total: <?= peso($total) ?></b></div>
    <h3>2. Payment Method</h3>
    <?php foreach ($PAY as $p): ?><label class="pay"><input type="radio" name="payment_method" value="<?= e($p) ?>" required> <?= e($p) ?></label><?php endforeach; ?>
    <div id="card" style="display:none">
      <h3>3. Card Details</h3>
      <label>Card Number<input name="card_number" inputmode="numeric" autocomplete="off" placeholder="4111 1111 1111 1111"></label>
      <div class="two"><label>Expiration Date (MM/YY)<input name="expiry" placeholder="08/28" autocomplete="off"></label>
      <label>CVV<input name="cvv" inputmode="numeric" maxlength="4" autocomplete="off"></label></div>
      <label>Card Holder Name<input name="holder" autocomplete="off"></label>
      <small>Card details are checked but never saved. TODO: connect a real payment gateway.</small>
    </div>
    <p><small>Gcash / Bank transfer / Paymaya / Cash: payment stays "pending" until confirmed by the company.</small></p>
    <a class="btn gray" href="book.php?step=details">Back</a> <button class="btn">Confirm Booking</button>
  </form>
  <script>
  const rs=document.querySelectorAll('input[name=payment_method]'),cd=document.getElementById('card');
  function sh(){const v=document.querySelector('input[name=payment_method]:checked');cd.style.display=(v&&v.value==='Visa')?'block':'none';}
  rs.forEach(x=>x.onchange=sh); sh();
  </script>
<?php endif; ?>
</section>

<aside class="summary">
  <div class="thumb"><?= car_img($car) ?></div>
  <h3><?= e($car['name']) ?></h3>
  <ul class="specs"><li><?= icon('users', 16) ?> <?= $car['seats'] ?> seats</li><li><?= icon('wheel', 16) ?> <?= e($car['transmission']) ?></li></ul>
  <div class="price"><?= peso($car['price_per_day']) ?> <small>/ day</small></div>
  <?php if ($total): ?><hr><div>Rental: <?= $days ?> day(s)</div><div><b>Total: <?= peso($total) ?></b></div><?php endif; ?>
</aside>
</div>
<?php include 'includes/footer.php'; ?>
