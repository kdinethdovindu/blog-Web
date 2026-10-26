<?php
require_once __DIR__ . '/../includes/init.php';
require_login(); // must be logged in

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_valid_csrf();

  if ($title === '' || mb_strlen($title) < 3)   $errors[] = 'Title must be at least 3 characters.';
  if ($content === '' || mb_strlen($content) < 10) $errors[] = 'Content must be at least 10 characters.';

  if (!$errors) {
    // make a UNIQUE slug
    $slug = slugify($title);
    $q = db()->prepare("SELECT 1 FROM posts WHERE slug=? LIMIT 1");
    while (true) {
      $q->execute([$slug]);
      if (!$q->fetch()) break;
      $slug = slugify($title); // regenerate until unique
    }

    $ins = db()->prepare("INSERT INTO posts (user_id, title, slug, content) VALUES (?,?,?,?)");
    $ins->execute([current_user()['id'], $title, $slug, $content]);

    flash('ok', 'Post created.');
    header('Location: '.base_url('post.php?slug='.$slug)); exit;
  }
}
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Create Post</h1>
<?php if ($errors): ?>
  <div style="color:#ef4444"><?php foreach ($errors as $e) echo '<div>'.e($e).'</div>'; ?></div>
<?php endif; ?>
<form method="post" style="display:grid;gap:16px;max-width:720px">
  <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
  <!-- title -->
  <div class="form-field">
    <input name="title" value="<?php echo e($title); ?>" placeholder=" " required>
    <label>Title</label>
  </div>

  <!-- markdown toolbar -->
  

  <!-- content -->
  <div class="form-field">
    <textarea name="content" rows="10" placeholder=" "><?php echo e($content); ?></textarea>
    <label>Content</label>
    <div class="form-hint">Write something nice – minimum 10 characters.</div>
  </div>
  <div class="editor-bar">
    <a href="#" id="toggle-preview" class="button">Toggle Preview</a>
    
  </div>

  <div id="preview-box" class="preview-wrap"></div>

  <button class="button primary" type="submit">Publish</button>

</form>
<p><a class="button" href="<?php echo base_url(); ?>">← Back</a></p>
<?php require_once __DIR__ . '/../includes/footer.php';
