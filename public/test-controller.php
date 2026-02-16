<?php
// Test controllers directly to see where they fail
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

echo "<h1>Controller Test</h1>";

// Make sure logged in
if (!Auth::check()) {
    echo "<p style='color:red'>Not logged in!</p>";
    echo "<p><a href='test-login-full.php'>Login first</a></p>";
    exit;
}

echo "<p style='color:green'>✓ Logged in as: " . htmlspecialchars(Auth::user()['username']) . "</p>";

// Test 1: Permission check
echo "<h2>Test 1: Permission Check</h2>";
try {
    $canView = Permission::can('view', 'venues');
    echo "<p>Can view venues: " . ($canView ? 'Yes' : 'No') . "</p>";

    if (!$canView) {
        echo "<p style='color:red'>✗ Permission denied! Your role (" . Auth::role() . ") cannot view venues.</p>";
        echo "<p><strong>Fix:</strong> The admin user should have permission. Check the Permission model.</p>";
    } else {
        echo "<p style='color:green'>✓ Permission granted</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Permission check failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Get venues data
echo "<h2>Test 2: Get Venues Data</h2>";
try {
    $venues = Venue::all();
    echo "<p style='color:green'>✓ Got " . count($venues) . " venues from database</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Failed to get venues: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test 3: Load venue view
echo "<h2>Test 3: Load Venue Index View</h2>";
try {
    $currentPage = 'venues';
    $pageTitle = 'Venues';

    ob_start();
    require __DIR__ . '/../src/views/venues/index.php';
    $output = ob_get_clean();

    echo "<p style='color:green'>✓ View loaded successfully (" . strlen($output) . " bytes)</p>";
    echo "<details><summary>Click to see rendered view</summary>";
    echo $output;
    echo "</details>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ View failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test 4: Call VenueController::index()
echo "<h2>Test 4: Call VenueController::index()</h2>";
echo "<p>Attempting to call the actual controller method...</p>";
try {
    ob_start();
    VenueController::index();
    $output = ob_get_clean();

    echo "<p style='color:green'>✓ Controller executed successfully</p>";
    echo "<details><summary>Click to see controller output</summary>";
    echo $output;
    echo "</details>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Controller failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='test-routes.php'>Back to Route Tests</a></p>";
?>
<style>
    body { font-family: sans-serif; padding: 2rem; max-width: 900px; margin: 0 auto; }
    details { margin: 1rem 0; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; }
    pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 4px; overflow-x: auto; }
</style>
