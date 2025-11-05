<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$u  = current_user();

// load post
$q = db()->prepare("SELECT * FROM posts WHERE id=? LIMIT 1");
$q->execute([$id]);
$post = $q->fetch();

if (!$post) { http_response_code(404); echo "<p>Post not found.</p>"; require __DIR__.'/../includes/footer.php'; exit; }
if ($post['user_id'] !== $u['id'] && $u['role'] !== 'admin') {
  http_response_code(403); echo "<p>Forbidden.</p>"; require __DIR__.'/../includes/footer.php'; exit;
}

$title = $post['title'];
$content = $post['content'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');

  if ($title === '' || mb_strlen($title) < 3)   $errors[] = 'Title must be at least 3 characters.';
  if ($content === '' || mb_strlen($content) < 10) $errors[] = 'Content must be at least 10 characters.';

  if (!$errors) {
    $upd = db()->prepare("UPDATE posts SET title=?, content=?, updated_at=NOW() WHERE id=?");
    $upd->execute([$title, $content, $id]);

    flash('ok', 'Post updated.');
    header('Location: '.base_url('post.php?slug='.$post['slug'])); exit;
  }
}
?>
<h1>Edit Post</h1>
<?php if ($errors): ?>
  <div style="color:#ef4444"><?php foreach ($errors as $e) echo '<div>'.e($e).'</div>'; ?></div>
<?php endif; ?>
<form method="post" style="display:grid;gap:16px;max-width:720px">
  <div class="form-field">
    <input name="title" value="<?php echo e($title); ?>" placeholder=" " required>
    <label>Title</label>
  </div>

  <div class="editor-bar">
    <a href="#" id="toggle-preview" class="button ghost">Toggle Preview</a>
    <span class="hint">Supports <code>#</code> headings, <code>**bold**</code>, <code>*italic*</code>, <code>`code`</code></span>
  </div>

  <div class="form-field">
    <textarea name="content" rows="10" placeholder=" "><?php echo e($content); ?></textarea>
    <label>Content</label>
  </div>

  <div id="preview-box" class="preview-wrap"></div>

  <button class="button success" type="submit">Save Changes</button>

</form>
<p><a class="button ghost" href="<?php echo base_url('mine.php'); ?>">← Back to My Posts</a></p>

<?php require_once __DIR__ . '/../includes/footer.php';
