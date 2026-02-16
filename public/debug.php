<?php
// Debug script to identify the exact issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>FM Checks Debug</h1>";

// Test 1: Basic PHP
echo "<h2>Test 1: Basic PHP</h2>";
echo "<p style='color:green'>✓ PHP is working</p>";

// Test 2: Session
echo "<h2>Test 2: Session Start</h2>";
try {
    session_start();
    echo "<p style='color:green'>✓ Session started successfully</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Session failed: " . $e->getMessage() . "</p>";
    die();
}

// Test 3: Database config file
echo "<h2>Test 3: Database Config</h2>";
try {
    require __DIR__ . '/../config/database.php';
    echo "<p style='color:green'>✓ Database config loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database config failed: " . $e->getMessage() . "</p>";
    die();
}

// Test 4: Database connection
echo "<h2>Test 4: Database Connection</h2>";
try {
    $pdo = Database::connect();
    echo "<p style='color:green'>✓ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Configure database.local.php and import schema</p>";
    die();
}

// Test 5: Load middleware
echo "<h2>Test 5: Load Middleware</h2>";
try {
    require __DIR__ . '/../src/middleware/Auth.php';
    require __DIR__ . '/../src/middleware/Csrf.php';
    echo "<p style='color:green'>✓ Middleware loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Middleware failed: " . $e->getMessage() . "</p>";
    die();
}

// Test 6: Load models
echo "<h2>Test 6: Load Models</h2>";
try {
    require __DIR__ . '/../src/models/User.php';
    require __DIR__ . '/../src/models/Permission.php';
    require __DIR__ . '/../src/models/Setting.php';
    echo "<p style='color:green'>✓ Models loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Models failed: " . $e->getMessage() . "</p>";
    die();
}

// Test 7: Test User model
echo "<h2>Test 7: Test User Model</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p style='color:green'>✓ User model works - {$result['count']} users in database</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ User query failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Import database schema: mysql -u root -p fmchecks &lt; database/schema.sql</p>";
    die();
}

// Test 8: Test a full page load
echo "<h2>Test 8: Full Request Simulation</h2>";
try {
    $_SERVER['REQUEST_URI'] = '/login';
    $_SERVER['REQUEST_METHOD'] = 'GET';

    require __DIR__ . '/../src/controllers/AuthController.php';

    echo "<p style='color:green'>✓ Controllers can be loaded</p>";
    echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
    echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Controller load failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    die();
}

echo "<h2>✓ All Tests Passed!</h2>";
echo "<p>The application should work. Try accessing <a href='/login'>/login</a></p>";
echo "<p>If you still get 500 error on the main site, check:</p>";
echo "<ul>";
echo "<li>PHP error logs</li>";
echo "<li>Apache/web server error logs</li>";
echo "<li>Check if .htaccess is causing issues (try renaming it temporarily)</li>";
echo "</ul>";
