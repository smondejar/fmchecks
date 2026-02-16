<?php
// Test all routes to see which ones work
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/middleware/Auth.php';

// Make sure user is logged in
if (!Auth::check()) {
    echo "<h1>Not Logged In</h1>";
    echo "<p><a href='test-login-full.php'>Login first</a></p>";
    exit;
}

$routes = [
    'Dashboard' => '/',
    'Login Page' => '/login',
    'Help' => '/help',
    'Venues' => '/venues',
    'Venues Create' => '/venues/create',
    'Areas' => '/areas',
    'Areas Create' => '/areas/create',
    'Check Types' => '/check-types',
    'Check Types Create' => '/check-types/create',
    'Checks' => '/checks',
    'Reports' => '/reports',
    'Reports Create' => '/reports/create',
    'Users' => '/users',
    'Settings' => '/settings',
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Route Testing</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; max-width: 1000px; margin: 0 auto; }
        .route-test { margin: 0.5rem 0; padding: 0.5rem; border: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .route-name { font-weight: bold; }
        .route-url { color: #666; font-family: monospace; }
        .test-btn { padding: 0.5rem 1rem; background: #2563eb; color: white; text-decoration: none; border-radius: 4px; font-size: 0.875rem; }
        .status { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; }
        .status-pending { background: #fef3c7; }
        .status-success { background: #d1fae5; color: #065f46; }
        .status-error { background: #fee2e2; color: #991b1b; }
        iframe { display: none; }
    </style>
</head>
<body>
    <h1>Route Testing Tool</h1>
    <p>Click "Test" to see if each route works. Green = works, Red = 500 error.</p>

    <div id="routes">
        <?php foreach ($routes as $name => $path): ?>
        <div class="route-test" id="route-<?= md5($path) ?>">
            <div>
                <div class="route-name"><?= htmlspecialchars($name) ?></div>
                <div class="route-url"><?= htmlspecialchars($path) ?></div>
            </div>
            <div>
                <span class="status status-pending" id="status-<?= md5($path) ?>">Not tested</span>
                <button class="test-btn" onclick="testRoute('<?= htmlspecialchars($path) ?>', '<?= md5($path) ?>')">Test</button>
                <a href="<?= htmlspecialchars($path) ?>" class="test-btn" style="background: #059669;">Open</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 2rem; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
        <h3>Instructions:</h3>
        <ol>
            <li>Click "Test" on each route to check if it works</li>
            <li>Green = Route works</li>
            <li>Red = Route has 500 error</li>
            <li>Click "Open" to see the actual error</li>
        </ol>
    </div>

    <iframe id="testFrame" name="testFrame"></iframe>

    <script>
        function testRoute(path, hash) {
            const statusEl = document.getElementById('status-' + hash);
            statusEl.textContent = 'Testing...';
            statusEl.className = 'status status-pending';

            const iframe = document.getElementById('testFrame');

            iframe.onload = function() {
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    const hasError = doc.body.innerHTML.includes('Internal Server Error') ||
                                   doc.body.innerHTML.includes('500') ||
                                   doc.title.includes('Error');

                    if (hasError) {
                        statusEl.textContent = '✗ Error 500';
                        statusEl.className = 'status status-error';
                    } else {
                        statusEl.textContent = '✓ Works';
                        statusEl.className = 'status status-success';
                    }
                } catch (e) {
                    // Cross-origin or other error
                    statusEl.textContent = '? Unknown';
                    statusEl.className = 'status status-pending';
                }
            };

            iframe.onerror = function() {
                statusEl.textContent = '✗ Failed';
                statusEl.className = 'status status-error';
            };

            iframe.src = path;
        }

        // Test all routes automatically
        window.onload = function() {
            const routes = <?= json_encode(array_values($routes)) ?>;
            const hashes = <?= json_encode(array_map('md5', array_values($routes))) ?>;

            let i = 0;
            function testNext() {
                if (i < routes.length) {
                    testRoute(routes[i], hashes[i]);
                    i++;
                    setTimeout(testNext, 1000);
                }
            }

            // Uncomment to auto-test all routes:
            // testNext();
        };
    </script>
</body>
</html>
