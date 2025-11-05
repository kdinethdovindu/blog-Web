<?php
require_once __DIR__ . '/../includes/init.php';

$title = 'Hello World';
$slug  = slugify($title);
$content = "This is your first blog post. You can edit or delete it later. Markdown-like content is fine here.";

$stmt = db()->prepare("INSERT INTO posts (user_id, title, slug, content) VALUES (?,?,?,?)");
$stmt->execute([1, $title, $slug, $content]); // user_id=1 (admin)

echo "Seeded: <a href='".base_url("post.php?slug={$slug}")."'>view post</a>";
