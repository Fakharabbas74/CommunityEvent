<?php
// Include the authentication helper (optional but useful for shared functions)
require_once 'includes/auth.php';

// Start the session to access session variables
session_start();

// Unset all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect the user to the login page after logout
header("Location: login.php");
exit(); // Ensure no further code is executed
?>
