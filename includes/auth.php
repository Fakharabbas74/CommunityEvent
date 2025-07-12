<?php
// Start a session to track user authentication status
session_start();

/**
 * Check if the user is currently logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login page if the user is not logged in
 * This helps protect pages that require authentication
 */
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit(); // Stop further script execution
    }
}

/**
 * Hash the user's password securely using bcrypt
 *
 * @param string $password Raw password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify the entered password against the hashed password
 *
 * @param string $password Raw password entered by the user
 * @param string $hashedPassword Hashed password from the database
 * @return bool True if passwords match, false otherwise
 */
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}
?>
