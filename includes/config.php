<?php
// =========================
// Database Configuration
// =========================

// Database host (usually 'localhost')
define('DB_HOST', 'localhost');

// Database username
define('DB_USER', 'root');

// Database password
define('DB_PASS', '');

// Name of the database
define('DB_NAME', 'community_events');

// Custom MySQL port (default is 3306, but changed here to 4306)
define('DB_PORT', '4306');

// =========================
// PDO Database Connection
// =========================
try {
    // Create a new PDO instance with host, port, and database name
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );

    // Set PDO error mode to throw exceptions for better debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Uncomment for debugging connection success
    // echo "Connected successfully!";
} catch (PDOException $e) {
    // If connection fails, stop script and show error
    die("ERROR: Could not connect. " . $e->getMessage());
}

// =========================
// Global Settings
// =========================

// Set default timezone for all date/time operations
date_default_timezone_set('UTC');
?>
