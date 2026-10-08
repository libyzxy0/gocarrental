<?php require '../config.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $id = (int)$_POST['id'];
    $s = db()->prepare('SELECT image FROM cars WHERE id=?'); $s->execute([$id]); $c = $s->fetch();
    if ($c) {
        try { db()->prepare('DELETE FROM cars WHERE id=?')->execute([$id]); delete_image($c['image']); flash('Car deleted.'); }
        catch (PDOException $ex) { flash('Cannot delete: this car has bookings.', 'err'); }
    }
}
redirect('/admin/index.php');
