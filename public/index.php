<?php
// MINIMAL TEST - Does index.php even execute?
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Index.php Minimal Test</h1>";
echo "<p>✓ PHP is executing!</p>";
echo "<p>REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>";
echo "<p>SCRIPT_NAME: " . htmlspecialchars($_SERVER['SCRIPT_NAME']) . "</p>";

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "<p>Parsed path: " . htmlspecialchars($path) . "</p>";

if ($path === '/venues') {
    echo "<h2>✓ MATCHED /venues!</h2>";
    echo "<p>Routing works! The issue is in the controller/view code.</p>";
} else {
    echo "<h2>Current path: " . htmlspecialchars($path) . "</h2>";
}

echo "<hr>";
echo "<p><a href='/venues'>Test /venues route</a></p>";
echo "<p><a href='/dashboard'>Test /dashboard route</a></p>";
?>
