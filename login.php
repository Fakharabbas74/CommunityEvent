<?php
// Include configuration file (e.g., for database connection)
require_once 'includes/config.php';

// Include authentication helper functions
require_once 'includes/auth.php';

// Redirect to admin page if the user is already logged in
if (isLoggedIn()) {
    header("Location: admin.php");
    exit();
}

// Initialize an array to store validation or login errors
$errors = [];

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and trim input values
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate input fields
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";

    // If no validation errors, proceed to verify credentials
    if (empty($errors)) {
        // Prepare and execute query to find user by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // If user found and password is correct
        if ($user && verifyPassword($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect to admin dashboard
            header("Location: admin.php");
            exit();
        } else {
            // Invalid credentials
            $errors[] = "Invalid username or password";
        }
    }
}
?>

<!-- HTML for login form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Link to external CSS file for styling -->
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Login</h2>

        <!-- Display any login or validation errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Display a success message from registration (if set) -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Login form -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>

        <!-- Link to registration page -->
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
