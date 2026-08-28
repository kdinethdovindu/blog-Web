<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  exit('Method not allowed.');
}

require_valid_csrf();

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
  http_response_code(400);
  exit('Bad request.');
}

$u = current_user();
// load the post to check ownership and get slug for message
$q = db()->prepare("SELECT id, user_id, title FROM posts WHERE id=? LIMIT 1");
$q->execute([$id]);
$post = $q->fetch();
if (!$post) { http_response_code(404); exit('Not found'); }

if ((int)$post['user_id'] !== (int)$u['id'] && $u['role'] !== 'admin') {
  http_response_code(403); exit('Forbidden');
}

$del = db()->prepare("DELETE FROM posts WHERE id=? LIMIT 1");
$del->execute([$id]);

flash('ok', 'Deleted: '.$post['title']);
header('Location: '.base_url('mine.php'));
exit;
