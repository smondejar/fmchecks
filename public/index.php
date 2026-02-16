<?php
// EMERGENCY MINIMAL TEST - No requires, no session, nothing
error_reporting(E_ALL);
ini_set('display_errors', 1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

echo "<!DOCTYPE html><html><head><title>Emergency Test</title></head><body>";
echo "<h1>✅ PHP Executing</h1>";
echo "<p><strong>Path:</strong> " . htmlspecialchars($path) . "</p>";
echo "<hr>";
echo "<p><a href='/'>Test /</a></p>";
echo "<p><a href='/venues'>Test /venues</a></p>";
echo "<p><a href='/debug-log.php'>Test /debug-log.php</a></p>";
echo "<p><a href='/direct-test.php'>Test /direct-test.php</a></p>";
echo "</body></html>";
?>
