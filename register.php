<?php
// Include the configuration file (e.g., database connection)
require_once 'includes/config.php';

// Include the authentication helper (e.g., session handling or utility functions)
require_once 'includes/auth.php';

// Check if the request method is POST (i.e., form was submitted)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim and store form inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Initialize an array to store validation errors
    $errors = [];

    // Basic validation rules
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords don't match";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";

    // Check if the username or email already exists in the database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) $errors[] = "Username or email already exists";

    // If there are no validation errors, proceed to register the user
    if (empty($errors)) {
        // Hash the password securely using a custom hashPassword() function
        $hashedPassword = hashPassword($password);

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashedPassword])) {
            // Set a success message in session and redirect to login page
            $_SESSION['success_message'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit(); // Terminate the script after redirect
        } else {
            // If insertion fails, show a generic error
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!-- HTML Part: Registration Form UI -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Link to external stylesheet for form styling -->
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Register</h2>

        <!-- Display validation errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Registration form -->
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>

        <!-- Link to login page -->
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
