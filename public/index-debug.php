<?php
// Debug version of index.php with detailed error reporting

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- ROUTER DEBUG START -->\n";
echo "<!-- Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . " -->\n";
echo "<!-- Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . " -->\n";

// Start session
try {
    session_start();
    echo "<!-- Session started: " . session_id() . " -->\n";
} catch (Exception $e) {
    die("Session Error: " . $e->getMessage());
}

// Load configuration and classes
try {
    require __DIR__ . '/../config/database.php';
    echo "<!-- Database config loaded -->\n";
} catch (Exception $e) {
    die("Database config error: " . $e->getMessage());
}

$requiredFiles = [
    'Auth' => __DIR__ . '/../src/middleware/Auth.php',
    'Csrf' => __DIR__ . '/../src/middleware/Csrf.php',
    'User' => __DIR__ . '/../src/models/User.php',
    'Permission' => __DIR__ . '/../src/models/Permission.php',
    'Setting' => __DIR__ . '/../src/models/Setting.php',
    'Venue' => __DIR__ . '/../src/models/Venue.php',
    'Area' => __DIR__ . '/../src/models/Area.php',
    'CheckType' => __DIR__ . '/../src/models/CheckType.php',
    'CheckPoint' => __DIR__ . '/../src/models/CheckPoint.php',
    'CheckLog' => __DIR__ . '/../src/models/CheckLog.php',
    'Report' => __DIR__ . '/../src/models/Report.php',
    'AuthController' => __DIR__ . '/../src/controllers/AuthController.php',
    'VenueController' => __DIR__ . '/../src/controllers/VenueController.php',
    'AreaController' => __DIR__ . '/../src/controllers/AreaController.php',
    'CheckTypeController' => __DIR__ . '/../src/controllers/CheckTypeController.php',
    'CheckController' => __DIR__ . '/../src/controllers/CheckController.php',
    'ReportController' => __DIR__ . '/../src/controllers/ReportController.php',
    'UserController' => __DIR__ . '/../src/controllers/UserController.php',
    'SettingsController' => __DIR__ . '/../src/controllers/SettingsController.php',
];

foreach ($requiredFiles as $name => $file) {
    try {
        require $file;
        echo "<!-- Loaded: $name -->\n";
    } catch (Exception $e) {
        die("Error loading $name: " . $e->getMessage());
    }
}

// Parse request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

echo "<!-- Parsed path: $path -->\n";
echo "<!-- Method: $method -->\n";
echo "<!-- ROUTER DEBUG END -->\n\n";

// Router
try {
    // Dashboard route
    if ($path === '/' || $path === '/dashboard') {
        echo "<!-- Routing to: Dashboard -->\n";
        Auth::requireAuth();
        require __DIR__ . '/../src/views/dashboard.php';
        exit;
    }

    // Auth routes
    if ($path === '/login') {
        echo "<!-- Routing to: Login -->\n";
        if ($method === 'GET') {
            AuthController::showLogin();
        } else {
            AuthController::login();
        }
        exit;
    }

    if ($path === '/logout') {
        echo "<!-- Routing to: Logout -->\n";
        AuthController::logout();
        exit;
    }

    // Venue routes
    if ($path === '/venues') {
        echo "<!-- Routing to: Venues Index -->\n";
        VenueController::index();
        exit;
    }

    if (preg_match('#^/venues/(\d+)$#', $path, $matches)) {
        echo "<!-- Routing to: Venue Show #{$matches[1]} -->\n";
        VenueController::show((int) $matches[1]);
        exit;
    }

    // 404 Not Found
    echo "<!-- No route matched for: $path -->\n";
    http_response_code(404);
    $errorCode = 404;
    $errorTitle = 'Page Not Found';
    $errorMessage = 'The page you are looking for does not exist.';
    require __DIR__ . '/../src/views/error.php';

} catch (Exception $e) {
    echo "\n<!-- EXCEPTION CAUGHT -->\n";
    echo "<!-- Message: " . htmlspecialchars($e->getMessage()) . " -->\n";
    echo "<!-- File: " . $e->getFile() . " -->\n";
    echo "<!-- Line: " . $e->getLine() . " -->\n";
    echo "<!-- Stack trace:\n" . htmlspecialchars($e->getTraceAsString()) . "\n-->\n";

    http_response_code(500);
    echo "<h1>Router Error</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
