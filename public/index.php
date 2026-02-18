<?php

// Start session
session_start();

// Load configuration and classes
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/middleware/Auth.php';
require __DIR__ . '/../src/middleware/Csrf.php';
require __DIR__ . '/../src/models/User.php';
require __DIR__ . '/../src/models/Permission.php';
require __DIR__ . '/../src/models/Setting.php';
require __DIR__ . '/../src/models/Venue.php';
require __DIR__ . '/../src/models/Area.php';
require __DIR__ . '/../src/models/CheckType.php';
require __DIR__ . '/../src/models/CheckPoint.php';
require __DIR__ . '/../src/models/CheckLog.php';
require __DIR__ . '/../src/models/Report.php';
require __DIR__ . '/../src/controllers/AuthController.php';
require __DIR__ . '/../src/controllers/VenueController.php';
require __DIR__ . '/../src/controllers/AreaController.php';
require __DIR__ . '/../src/controllers/CheckTypeController.php';
require __DIR__ . '/../src/controllers/CheckController.php';
require __DIR__ . '/../src/controllers/ReportController.php';
require __DIR__ . '/../src/controllers/UserController.php';
require __DIR__ . '/../src/controllers/SettingsController.php';
require __DIR__ . '/../src/controllers/AnalyticsController.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

set_exception_handler(function ($exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    $errorCode = 500;
    $errorTitle = 'Server Error';
    $errorMessage = 'An unexpected error occurred. Please try again later.';
    require __DIR__ . '/../src/views/error.php';
    exit;
});

// Parse request
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Router
try {
    // Dashboard route
    if ($path === '/' || $path === '/dashboard') {
        Auth::requireAuth();
        require __DIR__ . '/../src/views/dashboard.php';
        exit;
    }

    // Auth routes
    switch ($path) {
        case '/login':
            if ($method === 'GET') {
                AuthController::showLogin();
            } else {
                AuthController::login();
            }
            exit;

        case '/register':
            if ($method === 'GET') {
                AuthController::showRegister();
            } else {
                AuthController::register();
            }
            exit;

        case '/logout':
            AuthController::logout();
            exit;

        case '/help':
            Auth::requireAuth();
            require __DIR__ . '/../src/views/help.php';
            exit;
    }

    // Venue routes
    if (preg_match('#^/venues$#', $path)) {
        VenueController::index();
        exit;
    }
    if (preg_match('#^/venues/(\d+)$#', $path, $matches)) {
        VenueController::show((int) $matches[1]);
        exit;
    }
    if ($path === '/venues/create' && $method === 'GET') {
        VenueController::create();
        exit;
    }
    if ($path === '/venues/store' && $method === 'POST') {
        VenueController::store();
        exit;
    }
    if (preg_match('#^/venues/(\d+)/edit$#', $path, $matches) && $method === 'GET') {
        VenueController::edit((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/venues/(\d+)/update$#', $path, $matches) && $method === 'POST') {
        VenueController::update((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/venues/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
        VenueController::delete((int) $matches[1]);
        exit;
    }

    // Area routes
    if (preg_match('#^/areas$#', $path)) {
        AreaController::index();
        exit;
    }
    if (preg_match('#^/areas/(\d+)$#', $path, $matches)) {
        AreaController::show((int) $matches[1]);
        exit;
    }
    if ($path === '/areas/create' && $method === 'GET') {
        AreaController::create();
        exit;
    }
    if ($path === '/areas/store' && $method === 'POST') {
        AreaController::store();
        exit;
    }
    if (preg_match('#^/areas/(\d+)/edit$#', $path, $matches) && $method === 'GET') {
        AreaController::edit((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/areas/(\d+)/update$#', $path, $matches) && $method === 'POST') {
        AreaController::update((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/areas/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
        AreaController::delete((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/areas/(\d+)/calibrate$#', $path, $matches) && $method === 'POST') {
        AreaController::calibrate((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/areas/(\d+)/crop$#', $path, $matches) && $method === 'POST') {
        AreaController::saveCrop((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/areas/(\d+)/add-checkpoint$#', $path, $matches) && $method === 'POST') {
        AreaController::addCheckPoint((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/areas/(\d+)/checkpoints/(\d+)$#', $path, $matches) && $method === 'POST') {
        AreaController::updateCheckPoint((int) $matches[1], (int) $matches[2]);
        exit;
    }

    // Check Type routes
    if (preg_match('#^/check-types$#', $path)) {
        CheckTypeController::index();
        exit;
    }
    if ($path === '/check-types/create' && $method === 'GET') {
        CheckTypeController::create();
        exit;
    }
    if ($path === '/check-types/store' && $method === 'POST') {
        CheckTypeController::store();
        exit;
    }
    if (preg_match('#^/check-types/(\d+)/edit$#', $path, $matches) && $method === 'GET') {
        CheckTypeController::edit((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/check-types/(\d+)/update$#', $path, $matches) && $method === 'POST') {
        CheckTypeController::update((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/check-types/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
        CheckTypeController::delete((int) $matches[1]);
        exit;
    }

    // Check routes
    if (preg_match('#^/checks$#', $path)) {
        CheckController::index();
        exit;
    }
    if ($path === '/checks/export') {
        CheckController::export();
        exit;
    }
    if (preg_match('#^/checks/(\d+)/perform$#', $path, $matches) && $method === 'POST') {
        CheckController::perform((int) $matches[1]);
        exit;
    }

    // Analytics route
    if ($path === '/analytics') {
        AnalyticsController::index();
        exit;
    }

    // Report routes
    if (preg_match('#^/reports$#', $path)) {
        ReportController::index();
        exit;
    }
    if (preg_match('#^/reports/(\d+)$#', $path, $matches)) {
        ReportController::show((int) $matches[1]);
        exit;
    }
    if ($path === '/reports/create' && $method === 'GET') {
        ReportController::create();
        exit;
    }
    if ($path === '/reports/store' && $method === 'POST') {
        ReportController::store();
        exit;
    }
    if (preg_match('#^/reports/(\d+)/update$#', $path, $matches) && $method === 'POST') {
        ReportController::update((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/reports/(\d+)/resolve$#', $path, $matches) && $method === 'POST') {
        ReportController::resolve((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/reports/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
        ReportController::delete((int) $matches[1]);
        exit;
    }

    // User routes
    if (preg_match('#^/users$#', $path)) {
        UserController::index();
        exit;
    }
    if ($path === '/users/create' && $method === 'GET') {
        UserController::create();
        exit;
    }
    if ($path === '/users/store' && $method === 'POST') {
        UserController::store();
        exit;
    }
    if (preg_match('#^/users/(\d+)/edit$#', $path, $matches) && $method === 'GET') {
        UserController::edit((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/users/(\d+)/update$#', $path, $matches) && $method === 'POST') {
        UserController::update((int) $matches[1]);
        exit;
    }
    if (preg_match('#^/users/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
        UserController::delete((int) $matches[1]);
        exit;
    }

    // Settings routes
    if ($path === '/settings') {
        SettingsController::index();
        exit;
    }
    if ($path === '/settings/update' && $method === 'POST') {
        SettingsController::update();
        exit;
    }

    // 404 Not Found
    http_response_code(404);
    $errorCode = 404;
    $errorTitle = 'Page Not Found';
    $errorMessage = 'The page you are looking for does not exist.';
    require __DIR__ . '/../src/views/error.php';

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    $errorCode = 500;
    $errorTitle = 'Server Error';
    $errorMessage = 'An unexpected error occurred. Please try again later.';
    require __DIR__ . '/../src/views/error.php';
}
