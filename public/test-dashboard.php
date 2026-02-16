<?php
// Simple dashboard test
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/middleware/Auth.php';
require __DIR__ . '/../src/models/Permission.php';

if (!Auth::check()) {
    echo "<h1>Not Logged In</h1>";
    echo "<p>You are not logged in.</p>";
    echo "<p><a href='test-login-full.php'>Go to Login</a></p>";
    exit;
}

$user = Auth::user();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Test</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body style="padding: 2rem;">
    <h1>✓ Login Successful!</h1>
    <p>You are now logged in.</p>

    <h2>User Information:</h2>
    <ul>
        <li><strong>ID:</strong> <?= $user['id'] ?></li>
        <li><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></li>
        <li><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></li>
        <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
        <li><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></li>
    </ul>

    <h2>Next Steps:</h2>
    <p>The standalone test works! Now let's fix the main application.</p>

    <p>
        <a href="test-login-full.php" class="btn btn-secondary">Test Login Again</a>
        <a href="/" class="btn btn-primary">Try Main Application</a>
    </p>
</body>
</html>
