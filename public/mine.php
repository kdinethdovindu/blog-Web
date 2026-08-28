<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$u = current_user();
$stmt = db()->prepare("
  SELECT id, title, slug, created_at, updated_at
  FROM posts WHERE user_id=? ORDER BY created_at DESC
");
$stmt->execute([$u['id']]);
$posts = $stmt->fetchAll();
?>
<h1>My Posts</h1>

<div class="cards">
<?php if (!$posts): ?>
  <div class="card"><p>You haven’t written anything yet.</p></div>
<?php else: foreach ($posts as $p): ?>
  <div class="card">
    <h3><?php echo e($p['title']); ?></h3>
    <p class="meta">Created <?php echo date('Y-m-d', strtotime($p['created_at'])); ?>
      <?php if ($p['updated_at']): ?> • Updated <?php echo date('Y-m-d', strtotime($p['updated_at'])); ?><?php endif; ?>
    </p>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="button" href="<?php echo base_url('post.php?slug='.urlencode($p['slug'])); ?>">View</a>
      <a class="button success" href="<?php echo base_url('edit.php?id='.$p['id']); ?>">Edit</a>
      <form method="post" action="<?php echo base_url('delete.php'); ?>" onsubmit="return confirm('Delete this post? This cannot be undone.');">
        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <button class="button danger" type="submit">Delete</button>
      </form>
    </div>
  </div>
<?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
