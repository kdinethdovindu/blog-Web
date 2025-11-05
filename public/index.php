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

<section class="card hero" style="padding:22px 18px;margin-bottom:18px;text-align:center">
  <h1 style="margin:0 0 6px">Welcome to BlogApp</h1>
  <p class="meta">Share your thoughts with a modern, secure PHP blog.</p>
  <div class="hero-buttons">
    <a class="button primary" href="<?php echo base_url('new.php'); ?>">Write a Post</a>
    <a class="button ghost" href="<?php echo base_url('mine.php'); ?>">My Posts</a>
  </div>
</section>
<h1>Latest Posts</h1>

<p class="meta"></p>

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
    <div class="badges">
  <span class="badge blue">Public</span>
  <span class="badge purple">Story</span>
</div>

    
    <div><a class="button" href="<?php echo base_url('post.php?slug=' . urlencode($post['slug'])); ?>">Read</a></div>
  </div>
<?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
