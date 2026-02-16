<?php
// Diagnostic script for FM Checks

echo "<h1>FM Checks Diagnostic</h1>";

// Check PHP version
echo "<h2>1. PHP Version</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p style='color: " . (version_compare(PHP_VERSION, '8.0.0', '>=') ? 'green' : 'red') . "'>";
echo version_compare(PHP_VERSION, '8.0.0', '>=') ? "✓ PHP 8.0+ OK" : "✗ PHP 8.0+ Required";
echo "</p>";

// Check PDO
echo "<h2>2. PDO Extensions</h2>";
echo "<p>PDO: " . (extension_loaded('pdo') ? '<span style="color:green">✓ Loaded</span>' : '<span style="color:red">✗ Not loaded</span>') . "</p>";
echo "<p>PDO MySQL: " . (extension_loaded('pdo_mysql') ? '<span style="color:green">✓ Loaded</span>' : '<span style="color:red">✗ Not loaded</span>') . "</p>";

// Check required extensions
echo "<h2>3. Required Extensions</h2>";
$required = ['session', 'json', 'mbstring', 'fileinfo'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>$ext: " . ($loaded ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . "</p>";
}

// Check file permissions
echo "<h2>4. File Permissions</h2>";
$paths = [
    '../config/database.php' => 'readable',
    '../src/models/User.php' => 'readable',
    'uploads' => 'writable'
];
foreach ($paths as $path => $type) {
    if ($type === 'readable') {
        $ok = is_readable($path);
    } else {
        $ok = is_writable($path);
    }
    echo "<p>$path ($type): " . ($ok ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . "</p>";
}

// Check database config
echo "<h2>5. Database Configuration</h2>";
if (file_exists('../config/database.local.php')) {
    echo "<p style='color:green'>✓ database.local.php found</p>";
} elseif (file_exists('../config/database.example.php')) {
    echo "<p style='color:orange'>⚠ Using database.example.php (copy to database.local.php)</p>";
} else {
    echo "<p style='color:red'>✗ No database config found</p>";
}

// Try to connect to database
echo "<h2>6. Database Connection</h2>";
try {
    require_once '../config/database.php';
    $pdo = Database::connect();
    echo "<p style='color:green'>✓ Database connection successful</p>";

    // Test users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p style='color:green'>✓ Users table accessible ({$result['count']} users)</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Fix:</strong> Configure database.local.php and run: <code>mysql -u root -p fmchecks &lt; database/schema.sql</code></p>";
}

// Check .htaccess
echo "<h2>7. Apache Configuration</h2>";
if (file_exists('.htaccess')) {
    echo "<p style='color:green'>✓ .htaccess exists</p>";
} else {
    echo "<p style='color:red'>✗ .htaccess missing</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Fix any issues marked with ✗ above</li>";
echo "<li>Configure database credentials in config/database.local.php</li>";
echo "<li>Import database schema: <code>mysql -u root -p fmchecks &lt; database/schema.sql</code></li>";
echo "<li>Set upload folder permissions: <code>chmod -R 755 public/uploads</code></li>";
echo "<li>Access the application at <a href='/'>/ (home)</a></li>";
echo "</ol>";

echo "<p><small>Delete this file (diagnose.php) after setup is complete.</small></p>";
