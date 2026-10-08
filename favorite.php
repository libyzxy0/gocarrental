<?php require 'config.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $cid = (int)($_POST['car_id'] ?? 0); $uid = $_SESSION['user_id'];
    $s = db()->prepare('SELECT id FROM cars WHERE id=?'); $s->execute([$cid]);
    if ($s->fetch()) {
        $d = db()->prepare('DELETE FROM favorites WHERE user_id=? AND car_id=?'); $d->execute([$uid, $cid]);
        if (!$d->rowCount()) db()->prepare('INSERT IGNORE INTO favorites (user_id,car_id) VALUES (?,?)')->execute([$uid, $cid]);
    }
}
$back = $_POST['back'] ?? '';
if ($back && strpos($back, BASE_URL . '/') === 0 && strpos($back, '//') === false) { header('Location: ' . $back); exit; }
redirect('/favorites.php');
