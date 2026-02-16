<?php
// EMERGENCY MINIMAL TEST - No requires, no session, nothing
error_reporting(E_ALL);
ini_set('display_errors', 1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Write to log IMMEDIATELY
$logFile = __DIR__ . '/uploads/debug.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " REQUEST: " . $_SERVER['REQUEST_URI'] . " PATH: $path\n", FILE_APPEND);

echo "<!DOCTYPE html><html><head><title>Emergency Test</title></head><body>";
echo "<h1>✅ PHP Executing</h1>";
echo "<p><strong>Path:</strong> " . htmlspecialchars($path) . "</p>";
echo "<p><strong>Full URI:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>";
echo "<hr>";
echo "<h2>Test Different Routes:</h2>";
echo "<p><a href='/'>/ (root)</a></p>";
echo "<p><a href='/test'>Test /test</a></p>";
echo "<p><a href='/foobar'>Test /foobar</a></p>";
echo "<p><a href='/dashboard'>Test /dashboard</a></p>";
echo "<p><a href='/venues'>Test /venues</a></p>";
echo "<p><a href='/areas'>Test /areas</a></p>";
echo "<hr>";
echo "<p><a href='/debug-log.php'>View Debug Log</a></p>";
echo "</body></html>";
?>
