<?php require_once __DIR__ . '/init.php'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Blog App</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo base_url('../assets/css/app.css'); ?>">
<style>
/* tiny fallback if CSS file doesn’t load */
body{background:#0b1220;color:#e5e7eb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial;margin:0}
</style>
<script src="<?php echo base_url('../assets/js/app.js'); ?>" defer></script>
<script src="<?php echo base_url('../assets/js/editor.js'); ?>" defer></script>

</head>
<body class="<?php echo basename($_SERVER['PHP_SELF'], '.php') === 'index' ? 'page-home' :
(basename($_SERVER['PHP_SELF'], '.php') === 'new' ? 'page-create' :
(basename($_SERVER['PHP_SELF'], '.php') === 'edit' ? 'page-edit' : 'page-auth')); ?>">
<header class="site-header">
  <div class="container">
    <a class="brand" href="<?php echo base_url(); ?>">Blog<span>App</span></a>
    <nav class="nav">
      <a href="<?php echo base_url(); ?>">Home</a>
      <a href="<?php echo base_url('new.php'); ?>">Create</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?php echo base_url('mine.php'); ?>">My Posts</a>
        <span class="welcome">Hi, <?php echo e($_SESSION['user']['username']); ?></span>
        <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
      <?php else: ?>
        <a href="<?php echo base_url('register.php'); ?>">Register</a>
        <a href="<?php echo base_url('login.php'); ?>">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
<?php require __DIR__ . '/flash.php'; ?>

