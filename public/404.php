<?php
require_once __DIR__ . '/../includes/header.php';
http_response_code(404);
?>
<h1>Page not found</h1>
<p class="meta">The page you’re looking for doesn’t exist.</p>
<p><a class="button" href="<?php echo base_url(); ?>">Go Home</a></p>
<?php require_once __DIR__ . '/../includes/footer.php';
