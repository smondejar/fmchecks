<?php
// Log viewer - displays the debug log
$logFile = __DIR__ . '/uploads/debug.log';

header('Content-Type: text/plain');
echo "=== FM Checks Debug Log ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Log file: $logFile\n\n";

if (file_exists($logFile)) {
    echo "--- LOG CONTENTS ---\n";
    echo file_get_contents($logFile);
} else {
    echo "❌ Log file does not exist yet.\n";
    echo "Try accessing / or /venues first.\n";
}

echo "\n\n--- PHP INFO ---\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
?>
