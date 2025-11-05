<?php
$ok = flash('ok'); $err = flash('error');
if ($ok || $err): ?>
  <div class="flash <?php echo $ok ? 'flash-ok' : 'flash-err'; ?>">
    <div class="flash-dot"></div>
    <div class="flash-text"><?php echo e($ok ?: $err); ?></div>
  </div>
<?php endif; ?>
