<?php
require_once __DIR__ . '/../includes/init.php';
session_unset();
session_destroy();
session_start(); // start fresh for flash message
flash('ok', 'You have been logged out.');
header('Location: '.base_url());
exit;
