<?php
// MANAGED HOSTING COMPATIBLE DEBUG - Logs to web-accessible location
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log to uploads directory (web accessible)
$logFile = __DIR__ . '/uploads/debug.log';
$logDir = __DIR__ . '/uploads';

// Ensure uploads directory exists and is writable
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function debugLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

debugLog("=== NEW REQUEST ===");
debugLog("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NONE'));
debugLog("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NONE'));
debugLog("REDIRECT_STATUS: " . ($_SERVER['REDIRECT_STATUS'] ?? 'NONE'));
debugLog("REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'NONE'));

try {
    debugLog("Line 25: Starting execution");

    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    debugLog("Line 28: Got REQUEST_URI: $requestUri");

    $path = parse_url($requestUri, PHP_URL_PATH);
    debugLog("Line 31: Parsed path: $path");

    debugLog("Line 33: About to output HTML");

    echo "<!DOCTYPE html><html><head><title>Debug</title></head><body>";
    echo "<h1>Ultra Debug Test</h1>";
    echo "<p>✓ PHP is executing!</p>";
    echo "<p>REQUEST_URI: " . htmlspecialchars($requestUri) . "</p>";
    echo "<p>Parsed path: " . htmlspecialchars($path) . "</p>";

    debugLog("Line 42: About to match path");

    if ($path === '/') {
        debugLog("Line 45: Matched root");
        echo "<h2>✓ Matched root /</h2>";
    } elseif ($path === '/venues') {
        debugLog("Line 48: Matched /venues");
        echo "<h2>✓ MATCHED /venues!</h2>";
    } elseif ($path === '/dashboard') {
        debugLog("Line 51: Matched /dashboard");
        echo "<h2>✓ MATCHED /dashboard!</h2>";
    } else {
        debugLog("Line 54: No match, path=$path");
        echo "<h2>No match for: " . htmlspecialchars($path) . "</h2>";
    }

    echo "<hr>";
    echo "<h3>Test Links:</h3>";
    echo "<ul>";
    echo "<li><a href='/'>Test /</a></li>";
    echo "<li><a href='/venues'>Test /venues</a></li>";
    echo "<li><a href='/dashboard'>Test /dashboard</a></li>";
    echo "<li><a href='/debug-log.php'><strong>VIEW DEBUG LOG</strong></a></li>";
    echo "</ul>";

    echo "<p>✓ Reached end of script</p>";
    echo "</body></html>";

    debugLog("Line 71: Completed successfully");

} catch (Throwable $e) {
    debugLog("ERROR: " . $e->getMessage());
    debugLog("Stack: " . $e->getTraceAsString());
    echo "ERROR: " . htmlspecialchars($e->getMessage());
}
?>
