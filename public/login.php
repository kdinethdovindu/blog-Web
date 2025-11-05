<?php
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$identity = trim($_POST['identity'] ?? ''); // username or email
$pass     = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($identity === '' || $pass === '') $errors[] = 'Both fields are required.';
  if (!$errors) {
    $q = db()->prepare('SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1');
    $q->execute([$identity, $identity]);
    $u = $q->fetch();
    if (!$u || !password_verify($pass, $u['password_hash'])) {
      $errors[] = 'Invalid credentials.';
    } else {
      unset($u['password_hash']);
      $_SESSION['user'] = $u;
      flash('ok', 'Welcome back, '.$u['username'].'!');
      header('Location: '.base_url()); exit;
    }
  }
}
?>
<h1>Login</h1>
<?php if ($errors): ?>
  <div style="color:#ef4444"><?php foreach ($errors as $e) echo '<div>'.e($e).'</div>'; ?></div>
<?php endif; ?>
<form method="post" style="display:grid;gap:12px;max-width:420px">
  <input name="identity" value="<?php echo e($identity ?? ''); ?>" placeholder="Username or Email" required
         style="padding:10px;border-radius:10px;border:1px solid #263452;background:#0e1627;color:#e5e7eb">
  <input type="password" name="password" placeholder="Password" required
         style="padding:10px;border-radius:10px;border:1px solid #263452;background:#0e1627;color:#e5e7eb">
  <button class="button" type="submit">Login</button>
</form>
<p class="meta">No account? <a href="<?php echo base_url('register.php'); ?>">Register</a></p>
<?php require_once __DIR__ . '/../includes/footer.php';
