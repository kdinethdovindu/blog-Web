<?php
// Lightweight local environment health check.
$php_ok = version_compare(PHP_VERSION, '8.0.0', '>=');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Blog App — Health Check</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --bg:#0b1220; --card:#121a2b; --ok:#22c55e; --bad:#ef4444; --muted:#9aa4b2; --txt:#e5e7eb; --accent:#60a5fa; }
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;background:linear-gradient(120deg,#0b1220,#111827);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial;color:var(--txt);display:grid;place-items:center;padding:24px}
    .card{width:100%;max-width:720px;background:var(--card);border:1px solid #1f2a44;border-radius:18px;box-shadow:0 10px 40px rgba(0,0,0,.35);overflow:hidden}
    header{padding:24px;border-bottom:1px solid #1f2a44;display:flex;align-items:center;gap:12px}
    .dot{width:10px;height:10px;border-radius:50%;background:var(--accent);box-shadow:0 0 18px var(--accent)}
    h1{margin:0;font-size:20px;font-weight:700;letter-spacing:.3px}
    main{padding:24px;display:grid;gap:18px}
    .row{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .tag{padding:8px 12px;border-radius:999px;background:#0f172a; border:1px solid #263452;font-size:12px;color:var(--muted)}
    .status{font-weight:700}
    .ok{color:var(--ok)}
    .bad{color:var(--bad)}
    a.btn{display:inline-block;padding:12px 16px;border-radius:12px;background:#0f172a;border:1px solid #263452;color:#e5e7eb;text-decoration:none}
    a.btn:hover{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.2)}
    footer{padding:16px 24px;border-top:1px solid #1f2a44;color:var(--muted);font-size:12px}
    code{background:#0b1220;border:1px solid #263452;border-radius:8px;padding:2px 6px}
  </style>
</head>
<body>
  <div class="card">
    <header>
      <div class="dot"></div>
      <h1>Blog App — Local Health Check</h1>
    </header>
    <main>
      <div class="row">
        <div>Apache (localhost)</div>
        <div class="status ok">Running</div>
      </div>
      <div class="row">
        <div>PHP Version</div>
        <div class="status <?php echo $php_ok ? 'ok':'bad'; ?>">
          <?php echo PHP_VERSION; ?> <?php echo $php_ok ? '✓' : '✗ (need >= 8.0)'; ?>
        </div>
      </div>
      <div class="row">
        <div>Blog Application</div>
        <div><a class="btn" href="public/">Open</a></div>
      </div>
      <p class="tag">This page confirms that PHP is running. The blog also requires a configured MySQL database.</p>
    </main>
    <footer>Local URL: <code>http://localhost/blog-Web/</code></footer>
  </div>
</body>
</html>
