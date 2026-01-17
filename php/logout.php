<?php
require_once 'db.php';

// Destroy session
session_destroy();

// Redirect to home page
header('Location: ' . SITE_URL . '/index.php');
exit;
?>