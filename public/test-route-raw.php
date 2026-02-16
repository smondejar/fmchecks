<?php
// Bypass the router completely and test controller directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/middleware/Auth.php';
require __DIR__ . '/../src/middleware/Csrf.php';
require __DIR__ . '/../src/models/User.php';
require __DIR__ . '/../src/models/Permission.php';
require __DIR__ . '/../src/models/Setting.php';
require __DIR__ . '/../src/models/Venue.php';
require __DIR__ . '/../src/controllers/VenueController.php';

echo "<h1>Raw Route Test</h1>";
echo "<p>Testing VenueController::index() with NO error suppression...</p>";
echo "<hr>";

try {
    VenueController::index();
} catch (Throwable $e) {
    echo "<h2 style='color:red'>ERROR CAUGHT:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
