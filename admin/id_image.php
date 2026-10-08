<?php require '../config.php'; require_admin();
$s = db()->prepare('SELECT id_file FROM bookings WHERE id=?'); $s->execute([(int)($_GET['id'] ?? 0)]); $f = (string)$s->fetchColumn();
$path = PRIVATE_DIR . basename($f);
if ($f === '' || !is_file($path)) { http_response_code(404); exit('No file'); }
header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, max-age=0');
readfile($path);
