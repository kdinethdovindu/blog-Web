<?php
require_once __DIR__ . '/../includes/header.php';

// fetch latest posts with author
$posts = db()->query("
  SELECT p.id, p.title, p.slug,
         LEFT(p.content, 180) AS snippet,
         p.created_at,
         u.username AS author
  FROM posts p
  JOIN users u ON u.id = p.user_id
  ORDER BY p.created_at DESC
")->fetchAll();
?>
<h1>Latest Posts</h1>
<p class="meta">Welcome! This is your public home page.</p>

<div class="cards">
<?php if (!$posts): ?>
  <div class="card">
    <h3>No posts yet</h3>
    <p>Click <a class="button" href="<?php echo base_url('new.php'); ?>">Create</a> to add your first blog post.</p>
  </div>
<?php else: foreach ($posts as $post): ?>
  <div class="card">
    <h3><?php echo e($post['title']); ?></h3>
    <p><?php echo e($post['snippet']); ?>…</p>
    <p class="meta">by <?php echo e($post['author']); ?> • <?php echo date('Y-m-d', strtotime($post['created_at'])); ?></p>
    <div><a class="button" href="<?php echo base_url('post.php?slug=' . urlencode($post['slug'])); ?>">Read</a></div>
  </div>
<?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
