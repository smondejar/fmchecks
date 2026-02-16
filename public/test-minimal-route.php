<?php
// Absolute minimal routing test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "START<br>";

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

echo "Request URI: " . htmlspecialchars($requestUri) . "<br>";
echo "Parsed Path: " . htmlspecialchars($path) . "<br>";

if ($path === '/test-minimal-route.php') {
    echo "Direct access - OK<br>";
}

if (preg_match('#^/venues$#', $path)) {
    echo "MATCHED /venues route!<br>";
    echo "This proves routing works.<br>";
} else {
    echo "Did not match /venues<br>";
}

echo "END<br>";
?>
