<?php
// Direct access test - bypasses .htaccess rewrite
echo "<!DOCTYPE html><html><head><title>Direct Test</title></head><body>";
echo "<h1>✅ Direct PHP Access Works</h1>";
echo "<p>This file was accessed directly, bypassing .htaccess rewrites.</p>";

echo "<h2>Server Variables:</h2>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "REDIRECT_STATUS: " . ($_SERVER['REDIRECT_STATUS'] ?? 'N/A') . "\n";
echo "REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'N/A') . "\n";
echo "</pre>";

echo "<hr>";
echo "<h2>Test .htaccess rewrite:</h2>";
echo "<p>If you can see this page, direct PHP execution works.</p>";
echo "<p>Now test: <a href='/venues'>/venues</a></p>";
echo "<p>If /venues fails but this works, it's definitely .htaccess</p>";

echo "</body></html>";
?>
