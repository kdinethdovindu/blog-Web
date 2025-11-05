<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';
if (!$id || !$token || $token !== ($_SESSION['csrf'] ?? '')) { http_response_code(400); exit('Bad request.'); }

$u = current_user();
// load the post to check ownership and get slug for message
$q = db()->prepare("SELECT id, user_id, title FROM posts WHERE id=? LIMIT 1");
$q->execute([$id]);
$post = $q->fetch();
if (!$post) { http_response_code(404); exit('Not found'); }

if ($post['user_id'] !== $u['id'] && $u['role'] !== 'admin') {
  http_response_code(403); exit('Forbidden');
}

$del = db()->prepare("DELETE FROM posts WHERE id=? LIMIT 1");
$del->execute([$id]);

flash('ok', 'Deleted: '.$post['title']);
header('Location: '.base_url('mine.php'));
exit;
