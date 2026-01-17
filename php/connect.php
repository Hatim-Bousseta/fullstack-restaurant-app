<?php
require_once __DIR__ . '/db.php';

// Use the project's database helper which reads untracked config or environment variables
$conn = getDBConnection();

if (!$conn) {
    $e = oci_error();
    die("Connection failed: " . ($e['message'] ?? 'Unknown error'));
}

echo "Connected to Oracle successfully!";
?>