<?php
$ok = flash('ok'); $err = flash('error');
if ($ok || $err): ?>
  <div style="margin:10px 0;padding:12px;border-radius:10px;
    border:1px solid rgba(34,197,94,.35);background:rgba(34,197,94,.10);
    color:#e5e7eb;">
    <?php if ($ok) echo e($ok); ?>
    <?php if ($err) echo '<div style="color:#ef4444">'.e($err).'</div>'; ?>
  </div>
<?php endif; ?>

