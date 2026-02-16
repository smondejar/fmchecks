<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Server Debug Info</h1>";
echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";
echo "<h2>Request Details:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Variable</th><th>Value</th></tr>";

$vars = [
    'REQUEST_URI',
    'SCRIPT_NAME',
    'PHP_SELF',
    'PATH_INFO',
    'QUERY_STRING',
    'REQUEST_METHOD',
    'REDIRECT_STATUS',
    'REDIRECT_URL'
];

foreach ($vars as $var) {
    $value = isset($_SERVER[$var]) ? $_SERVER[$var] : 'NOT SET';
    echo "<tr><td>$var</td><td>" . htmlspecialchars($value) . "</td></tr>";
}

echo "</table>";

echo "<h2>Test URL Parsing:</h2>";
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
echo "<p>Original URI: <code>" . htmlspecialchars($requestUri) . "</code></p>";
echo "<p>Parsed Path: <code>" . htmlspecialchars($path) . "</code></p>";

echo "<h2>Pattern Tests:</h2>";
echo "<p>/venues match: " . (preg_match('#^/venues$#', $path) ? 'YES' : 'NO') . "</p>";
echo "<p>/dashboard match: " . (($path === '/' || $path === '/dashboard') ? 'YES' : 'NO') . "</p>";

echo "<h2>Next Step:</h2>";
echo "<p>Now visit: <a href='/venues'>/venues</a> and see what values appear above.</p>";
?>
