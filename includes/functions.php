<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($path) { header('Location: ' . BASE_URL . $path); exit; }
function peso($n) { return '₱' . number_format((float)$n, 0); }

function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']);
        return "<div class='flash $t'>" . e($m) . "</div>";
    }
    return '';
}

// ---- Auth ----
function is_logged_in() { return !empty($_SESSION['user_id']); }
function current_user() {
    static $u = null;
    if (!is_logged_in()) return null;
    if (!$u) { $s = db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$_SESSION['user_id']]); $u = $s->fetch(); }
    return $u;
}
function require_login() { if (!is_logged_in()) { $_SESSION['after_login'] = $_SERVER['REQUEST_URI']; flash('Please log in first.', 'err'); redirect('/login.php'); } }
function require_admin() {
    require_login();
    if (current_user()['role'] !== 'admin') { http_response_code(403); exit('Admins only.'); }
}

// ---- CSRF ----
function csrf_token() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field() { return "<input type='hidden' name='csrf' value='" . csrf_token() . "'>"; }
function csrf_check() {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) { http_response_code(400); exit('Invalid request.'); }
}

// ---- Images ----
function upload_image($file) {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload failed.');
    if ($file['size'] > 5 * 1024 * 1024) throw new Exception('Image must be under 5MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) throw new Exception('Only JPG, PNG or WEBP allowed.');
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) throw new Exception('Could not save image.');
    return $name;
}
function delete_image($name) { if ($name && is_file(UPLOAD_DIR . $name)) unlink(UPLOAD_DIR . $name); }



// ---- Booking helpers ----
function upload_private_id($file) {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('ID upload failed.');
    if ($file['size'] > 5 * 1024 * 1024) throw new Exception('ID image must be under 5MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png'][$mime] ?? null;
    if (!$ext) throw new Exception('ID must be a JPG or PNG image.');
    $name = bin2hex(random_bytes(10)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], PRIVATE_DIR . $name)) throw new Exception('Could not save ID.');
    return $name;
}
function delete_private($name) { if ($name && is_file(PRIVATE_DIR . $name)) unlink(PRIVATE_DIR . $name); }

function valid_date($d) { $x = DateTime::createFromFormat('Y-m-d', (string)$d); return $x && $x->format('Y-m-d') === $d; }
function rental_days($from, $to) { return max(1, (new DateTime($from))->diff(new DateTime($to))->days); }
function car_available($car_id, $from, $to, $exclude = 0) {
    $s = db()->prepare("SELECT COUNT(*) FROM bookings WHERE car_id=? AND status='confirmed' AND pickup_date <= ? AND dropoff_date >= ? AND id<>?");
    $s->execute([$car_id, $to, $from, $exclude]);
    return (int)$s->fetchColumn() === 0;
}


// ---- UI helpers ----
function icon($n, $s = 18, $fill = false) {
    $p = [
     'users'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
     'wheel'=>'<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="2.5"/><path d="M12 14.5V22M9.8 11 2.5 9.5M14.2 11l7.3-1.5"/>',
     'fuel'=>'<path d="M3 22V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v17"/><path d="M15 10h2a2 2 0 0 1 2 2v3a2 2 0 0 0 4 0V8l-3-3"/><path d="M3 22h12M7 8h4"/>',
     'pin'=>'<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
     'cal'=>'<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
     'car'=>'<path d="M3 17h18M4 13l2-6a2 2 0 0 1 2-1h8a2 2 0 0 1 2 1l2 6v4H4z"/><circle cx="7.5" cy="17" r="1"/><circle cx="16.5" cy="17" r="1"/>',
     'arrow'=>'<path d="M5 12h14M13 6l6 6-6 6"/>',
     'back'=>'<path d="M19 12H5M11 6l-6 6 6 6"/>',
     'check'=>'<path d="M20 6 9 17l-5-5"/>',
     'shield'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
     'headset'=>'<path d="M3 14v-2a9 9 0 0 1 18 0v2"/><path d="M21 15a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2zM3 15a2 2 0 0 0 2 2h1v-5H5a2 2 0 0 0-2 2z"/><path d="M18 17v1a3 3 0 0 1-3 3h-2"/>',
     'tag'=>'<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1.5"/>',
     'zap'=>'<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>',
     'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
     'chev'=>'<path d="m6 9 6 6 6-6"/>',
     'heart'=>'<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
     'star'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
          'mail'=>'<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
     'clock'=>'<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
     'phone'=>'<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
    ];
    return "<svg class='ic' width='$s' height='$s' viewBox='0 0 24 24' fill='" . ($fill ? 'currentColor' : 'none') . "' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>" . ($p[$n] ?? '') . "</svg>";
}
function car_svg() {
    return '<svg viewBox="0 0 120 80" fill="currentColor" aria-hidden="true"><path d="M24 33l7-17c1-3 4-5 7-5h44c3 0 6 2 7 5l7 17c6 1 10 5 10 11v16c0 2-2 4-4 4h-9c-2 0-4-2-4-4v-3H35v3c0 2-2 4-4 4h-9c-2 0-4-2-4-4V44c0-6 4-10 10-11z"/><path style="fill:var(--win,#fff);opacity:.6" d="M38 19h44l5 13H33z"/><circle style="fill:var(--win,#fff)" cx="32" cy="46" r="5"/><circle style="fill:var(--win,#fff)" cx="88" cy="46" r="5"/><rect style="fill:var(--win,#fff)" x="48" y="45" width="24" height="4" rx="2"/></svg>';
}
function car_img($car) {
    if (!empty($car['image'])) return "<img src='" . BASE_URL . "/uploads/" . e($car['image']) . "' alt='" . e($car['name'] ?? '') . "' loading='lazy'>";
    return "<span class='noimg'>" . car_svg() . "</span>";
}


// ---- Favorites / reviews helpers ----
function fav_ids() {
    static $ids = null;
    if ($ids === null) {
        $ids = [];
        if (is_logged_in()) { $s = db()->prepare('SELECT car_id FROM favorites WHERE user_id=?'); $s->execute([$_SESSION['user_id']]); $ids = array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN)); }
    }
    return $ids;
}
function heart_btn($car_id) {
    $on = in_array((int)$car_id, fav_ids(), true); $u = BASE_URL; $cls = 'heart' . ($on ? ' on' : '');
    if (!is_logged_in()) return "<a class='$cls' href='$u/login.php' title='Log in to save favorites' aria-label='Log in to save favorites'>" . icon('heart', 18) . "</a>";
    $label = $on ? 'Remove from favorites' : 'Save to favorites';
    return "<form class='fav' method='post' action='$u/favorite.php'>" . csrf_field() . "<input type='hidden' name='car_id' value='" . (int)$car_id . "'><input type='hidden' name='back' value='" . e($_SERVER['REQUEST_URI']) . "'><button class='$cls' title='$label' aria-label='$label'>" . icon('heart', 18, $on) . "</button></form>";
}
function stars($n) {
    $o = "<span class='stars' aria-label='" . (int)$n . " out of 5'>";
    for ($i = 1; $i <= 5; $i++) $o .= "<span class='s" . ($i <= $n ? ' on' : '') . "'>" . icon('star', 16, true) . "</span>";
    return $o . "</span>";
}
function car_rating($car_id) {
    $s = db()->prepare('SELECT AVG(rating), COUNT(*) FROM reviews WHERE car_id=?'); $s->execute([$car_id]); $r = $s->fetch(PDO::FETCH_NUM);
    return [round((float)$r[0], 1), (int)$r[1]];
}
function reviewer_name($full) { $p = preg_split('/\s+/', trim($full)); return count($p) > 1 ? $p[0] . ' ' . mb_substr(end($p), 0, 1) . '.' : $p[0]; }

function car_card($c) {
    $u = BASE_URL;
    $badge = $c['badge'] ? "<span class='badge'>" . e($c['badge']) . "</span>" : '';
    return "<article class='card'>
      <div class='thumbwrap'><a class='thumb' href='$u/car.php?id={$c['id']}'>$badge" . car_img($c) . "</a>" . heart_btn($c['id']) . "</div>
      <div class='card-body'>
        <div class='card-title'><h3>" . e($c['name']) . "</h3><span class='tag'>" . e($c['category']) . "</span></div>
        <ul class='specs'><li>" . icon('users', 16) . " {$c['seats']} seats</li><li>" . icon('wheel', 16) . " " . e($c['transmission']) . "</li><li>" . icon('fuel', 16) . " " . e($c['fuel']) . "</li></ul>
        <div class='card-foot'><div class='price'>" . peso($c['price_per_day']) . "<small>/day</small></div>
        <a class='btn small' href='$u/car.php?id={$c['id']}'>Rent now " . icon('arrow', 16) . "</a></div>
      </div></article>";
}
