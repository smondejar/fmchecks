<?php
// Complete standalone login test with error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load dependencies
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/middleware/Auth.php';
require __DIR__ . '/../src/middleware/Csrf.php';
require __DIR__ . '/../src/models/User.php';
require __DIR__ . '/../src/models/Permission.php';
require __DIR__ . '/../src/models/Setting.php';
require __DIR__ . '/../src/controllers/AuthController.php';

// Handle POST (login attempt)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Login...</h2>";
    echo "<pre>";
    echo "POST data received:\n";
    echo "Username: " . htmlspecialchars($_POST['username'] ?? 'not set') . "\n";
    echo "Password: " . (isset($_POST['password']) ? '[HIDDEN]' : 'not set') . "\n";
    echo "CSRF Token: " . (isset($_POST['csrf_token']) ? 'present' : 'MISSING') . "\n";
    echo "</pre>";

    try {
        // Validate CSRF
        if (!Csrf::validate()) {
            echo "<p style='color:red'>CSRF validation failed!</p>";
            Csrf::regenerate();
        } else {
            echo "<p style='color:green'>CSRF validation passed</p>";
        }

        // Try authentication
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        echo "<p>Attempting to authenticate user: " . htmlspecialchars($username) . "</p>";

        $user = User::authenticate($username, $password);

        if ($user) {
            echo "<p style='color:green'>✓ Authentication successful!</p>";
            echo "<pre>" . print_r($user, true) . "</pre>";

            Auth::login($user);
            echo "<p style='color:green'>✓ User logged in!</p>";
            echo "<p><a href='test-dashboard.php'>Go to Dashboard Test</a></p>";
        } else {
            echo "<p style='color:red'>✗ Authentication failed - invalid credentials</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

    echo "<hr><p><a href='test-login-full.php'>Back to login</a></p>";
    exit;
}

// Show login form (GET request)
$pageTitle = 'Login Test';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <h1>FM Checks Login Test</h1>
                <p>Standalone test with debugging</p>
            </div>

            <div class="auth-card">
                <h2>Login</h2>
                <form method="POST" action="test-login-full.php">
                    <?= Csrf::field() ?>

                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" class="form-control" value="admin" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" value="admin123" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>

                <p style="margin-top: 1rem; font-size: 0.875rem; color: #666;">
                    <strong>Default credentials:</strong><br>
                    Username: admin<br>
                    Password: admin123
                </p>
            </div>
        </div>
    </div>
</body>
</html>
