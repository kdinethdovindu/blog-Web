<?php
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$identity = trim($_POST['identity'] ?? ''); // username or email
$pass     = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_valid_csrf();

  if ($identity === '' || $pass === '') $errors[] = 'Both fields are required.';
  if (!$errors) {
    $q = db()->prepare('SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1');
    $q->execute([$identity, $identity]);
    $u = $q->fetch();
    if (!$u || !password_verify($pass, $u['password_hash'])) {
      $errors[] = 'Invalid credentials.';
    } else {
      unset($u['password_hash']);
      session_regenerate_id(true);
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
<form method="post" style="display:grid;gap:16px;max-width:420px">
  <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
  <div class="form-field">
    <input name="identity" value="<?php echo e($identity ?? ''); ?>" placeholder=" " required>
    <label>Username or Email</label>
  </div>
  <div class="form-field">
    <input type="password" name="password" placeholder=" " required>
    <label>Password</label>
  </div>
  <button class="button primary" type="submit">Login</button>
</form>
<p class="meta">No account? <a href="<?php echo base_url('register.php'); ?>">Register</a></p>

<?php require_once __DIR__ . '/../includes/footer.php';
