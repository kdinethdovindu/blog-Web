<?php
require_once __DIR__ . '/../includes/header.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
  http_response_code(404);
  echo "<p>Missing slug.</p>";
  require_once __DIR__ . '/../includes/footer.php'; exit;
}

$q = db()->prepare("
SELECT p.*, u.username AS author
FROM posts p
JOIN users u ON u.id = p.user_id
WHERE p.slug = ?
LIMIT 1
");
$q->execute([$slug]);
$post = $q->fetch();

if (!$post) {
  http_response_code(404);
  echo "<p>Post not found.</p>";
  require_once __DIR__ . '/../includes/footer.php'; exit;
}
?>
<article class="card">
  <h1><?php echo e($post['title']); ?></h1>
  <p class="meta">by <?php echo e($post['author']); ?> • <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></p>
  <p><?php echo nl2br(e($post['content'])); ?></p>
  
  <?php if (current_user() && (current_user()['id'] === (int)$post['user_id'] || current_user()['role'] === 'admin')): ?>
  <div style="display:flex;gap:8px;margin-top:10px">
    <a class="button" href="<?php echo base_url('edit.php?id='.$post['id']); ?>">Edit</a>
    <a class="button" href="<?php echo base_url('delete.php?id='.$post['id'].'&token='.urlencode($_SESSION['csrf'] ?? '')); ?>"
       onclick="return confirm('Delete this post?');">Delete</a>
  </div>
<?php endif; ?>


</article>
<p><a class="button" href="<?php echo base_url(); ?>">← Back to Home</a></p>

<?php require_once __DIR__ . '/../includes/footer.php';
