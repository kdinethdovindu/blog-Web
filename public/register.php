<?php
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass     = $_POST['password'] ?? '';
$pass2    = $_POST['confirm'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // basic validation
  if ($username === '' || !preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) $errors[] = 'Username must be 3-20 chars (letters, numbers, underscore).';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
  if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
  if ($pass !== $pass2) $errors[] = 'Passwords do not match.';

  if (!$errors) {
    // unique checks
    $q = db()->prepare('SELECT 1 FROM users WHERE username = ? OR email = ? LIMIT 1');
    $q->execute([$username, $email]);
    if ($q->fetch()) {
      $errors[] = 'Username or email already exists.';
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $ins = db()->prepare('INSERT INTO users (username, email, password_hash) VALUES (?,?,?)');
      $ins->execute([$username, $email, $hash]);

      // fetch new user & login
      $u = db()->query('SELECT id, username, email, role FROM users ORDER BY id DESC LIMIT 1')->fetch();
      $_SESSION['user'] = $u;
      flash('ok', 'Registration successful. Welcome, '.$u['username'].'!');
      header('Location: '.base_url()); exit;
    }
  }
}
?>
<h1>Create Account</h1>
<?php if ($errors): ?>
  <div style="color:#ef4444"><?php foreach ($errors as $e) echo '<div>'.e($e).'</div>'; ?></div>
<?php endif; ?>
<form method="post" style="display:grid;gap:12px;max-width:480px">
  <input name="username" value="<?php echo e($username); ?>" placeholder="Username" required
         style="padding:10px;border-radius:10px;border:1px solid #263452;background:#0e1627;color:#e5e7eb">
  <input type="email" name="email" value="<?php echo e($email); ?>" placeholder="Email" required
         style="padding:10px;border-radius:10px;border:1px solid #263452;background:#0e1627;color:#e5e7eb">
  <input type="password" name="password" placeholder="Password (min 8 chars)" required
         style="padding:10px;border-radius:10px;border:1px solid #263452;background:#0e1627;color:#e5e7eb">
  <input type="password" name="confirm" placeholder="Confirm password" required
         style="padding:10px;border-radius:10px;border:1px solid #263452;background:#0e1627;color:#e5e7eb">
  <button class="button" type="submit">Register</button>
</form>
<p class="meta">Already have an account? <a href="<?php echo base_url('login.php'); ?>">Log in</a></p>
<?php require_once __DIR__ . '/../includes/footer.php';
