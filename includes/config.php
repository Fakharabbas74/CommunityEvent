<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'community_events');
define('DB_PORT', '4306'); // Custom port

// Create connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully!";
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Set timezone
date_default_timezone_set('UTC');
?>
