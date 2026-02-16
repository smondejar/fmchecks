<?php
// ULTRA VERBOSE TEST
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-errors.log');

// Log to file
file_put_contents('/tmp/fmchecks-debug.log', date('Y-m-d H:i:s') . " - Request: " . ($_SERVER['REQUEST_URI'] ?? 'NO URI') . "\n", FILE_APPEND);

echo "<!DOCTYPE html><html><head><title>Debug</title></head><body>";
echo "<h1>Index.php Ultra Verbose Test</h1>";
echo "<p>✓ Line 10: PHP is executing!</p>";

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
echo "<p>✓ Line 13: Got REQUEST_URI: " . htmlspecialchars($requestUri) . "</p>";

$path = parse_url($requestUri, PHP_URL_PATH);
echo "<p>✓ Line 16: Parsed path: " . htmlspecialchars($path ?? 'NULL') . "</p>";

echo "<p>✓ Line 18: About to test path matching...</p>";

if ($path === '/') {
    echo "<h2>✓ Matched root /</h2>";
} elseif ($path === '/venues') {
    echo "<h2>✓ MATCHED /venues!</h2>";
    echo "<p>If you see this, routing works perfectly!</p>";
} elseif ($path === '/dashboard') {
    echo "<h2>✓ MATCHED /dashboard!</h2>";
    echo "<p>If you see this, routing works perfectly!</p>";
} else {
    echo "<h2>Path: " . htmlspecialchars($path) . "</h2>";
    echo "<p>No specific match</p>";
}

echo "<p>✓ Line 33: Finished path matching</p>";

echo "<hr>";
echo "<h3>Test Links:</h3>";
echo "<p><a href='/'>Test / (root)</a></p>";
echo "<p><a href='/venues'>Test /venues</a></p>";
echo "<p><a href='/dashboard'>Test /dashboard</a></p>";
echo "<p><a href='/phpinfo.php'>View phpinfo()</a></p>";

echo "<p>✓ Line 43: Reached end of script</p>";
echo "</body></html>";

file_put_contents('/tmp/fmchecks-debug.log', date('Y-m-d H:i:s') . " - Completed successfully\n", FILE_APPEND);
?>
