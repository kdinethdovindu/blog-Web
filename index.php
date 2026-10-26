<?php
// Direct local Apache visitors to the application; Vercel uses api/index.php.
header('Location: public/', true, 302);
exit;
