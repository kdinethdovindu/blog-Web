<?php
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass     = $_POST['password'] ?? '';
$pass2    = $_POST['confirm'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_valid_csrf();

  // basic validation
  if ($username === '' || !preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) $errors[] = 'Username must be 3-20 chars (letters, numbers, underscore).';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
  if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
  if ($pass !== $pass2) $errors[] = 'Passwords do not match.';

  if (!$errors) {
    $db = db();

    // unique checks
    $q = $db->prepare('SELECT 1 FROM users WHERE username = ? OR email = ? LIMIT 1');
    $q->execute([$username, $email]);
    if ($q->fetch()) {
      $errors[] = 'Username or email already exists.';
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $ins = $db->prepare('INSERT INTO users (username, email, password_hash) VALUES (?,?,?)');
      $ins->execute([$username, $email, $hash]);

      $userId = $db->lastInsertId();
      $userQuery = $db->prepare('SELECT id, username, email, role FROM users WHERE id = ? LIMIT 1');
      $userQuery->execute([$userId]);
      $user = $userQuery->fetch();

      if (!$user) {
        $errors[] = 'Your account was created, but automatic login failed. Please log in.';
      } else {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        flash('ok', 'Registration successful. Welcome, ' . $user['username'] . '!');
        header('Location: ' . base_url());
        exit;
      }
    }
  }
}
?>
<h1>Create Account</h1>
<?php if ($errors): ?>
  <div style="color:#ef4444"><?php foreach ($errors as $e) echo '<div>'.e($e).'</div>'; ?></div>
<?php endif; ?>
<form method="post" style="display:grid;gap:16px;max-width:480px">
  <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
  <div class="form-field">
    <input name="username" value="<?php echo e($username); ?>" placeholder=" " required>
    <label>Username</label>
  </div>
  <div class="form-field">
    <input type="email" name="email" value="<?php echo e($email); ?>" placeholder=" " required>
    <label>Email</label>
  </div>
  <div class="form-field">
    <input type="password" name="password" placeholder=" " required>
    <label>Password (min 8 chars)</label>
  </div>
  <div class="form-field">
    <input type="password" name="confirm" placeholder=" " required>
    <label>Confirm Password</label>
  </div>
  <button class="button primary" type="submit">Register</button>
</form>
<p class="meta">Already have an account? <a href="<?php echo base_url('login.php'); ?>">Log in</a></p>

<?php require_once __DIR__ . '/../includes/footer.php';
