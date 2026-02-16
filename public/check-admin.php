<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../config/database.php';

echo "<h1>Admin User Check</h1>";

try {
    $pdo = Database::connect();

    // Check if users table exists
    echo "<h2>1. Users Table</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Users table exists</p>";
    } else {
        echo "<p style='color:red'>✗ Users table does NOT exist!</p>";
        echo "<p><strong>Fix:</strong> Import the schema: <code>mysql -u username -p dbname &lt; database/schema.sql</code></p>";
        exit;
    }

    // Count users
    echo "<h2>2. User Count</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Total users: <strong>{$result['count']}</strong></p>";

    if ($result['count'] == 0) {
        echo "<p style='color:red'>✗ No users in database!</p>";
        echo "<p><strong>Fix:</strong> The schema wasn't fully imported. Re-import it.</p>";
        exit;
    }

    // Check for admin user
    echo "<h2>3. Admin User</h2>";
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role, is_active FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();

    if ($admin) {
        echo "<p style='color:green'>✓ Admin user found</p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$admin['id']}</li>";
        echo "<li><strong>Username:</strong> {$admin['username']}</li>";
        echo "<li><strong>Email:</strong> {$admin['email']}</li>";
        echo "<li><strong>Role:</strong> {$admin['role']}</li>";
        echo "<li><strong>Active:</strong> " . ($admin['is_active'] ? 'Yes' : 'No') . "</li>";
        echo "<li><strong>Password Hash:</strong> " . substr($admin['password_hash'], 0, 20) . "...</li>";
        echo "</ul>";

        // Test password verification
        echo "<h2>4. Password Verification Test</h2>";
        $testPassword = 'admin123';
        echo "<p>Testing password: <code>admin123</code></p>";

        if (password_verify($testPassword, $admin['password_hash'])) {
            echo "<p style='color:green'>✓ Password verification WORKS!</p>";
            echo "<p>This is strange - the password should work. Let me check the User model...</p>";
        } else {
            echo "<p style='color:red'>✗ Password verification FAILED!</p>";
            echo "<p>The password hash in the database doesn't match 'admin123'.</p>";
            echo "<h3>Solution: Reset Admin Password</h3>";

            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "<p>Run this SQL to fix it:</p>";
            echo "<pre style='background:#f0f0f0;padding:1rem;'>UPDATE users SET password_hash = '{$newHash}' WHERE username = 'admin';</pre>";

            echo "<p>Or click here to auto-fix:</p>";
            echo "<form method='POST'>";
            echo "<button type='submit' name='fix_password' class='btn btn-primary'>Reset Admin Password to 'admin123'</button>";
            echo "</form>";

            if (isset($_POST['fix_password'])) {
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
                $stmt->execute([$newHash]);
                echo "<p style='color:green;font-weight:bold'>✓ Password reset! Refresh this page.</p>";
            }
        }

    } else {
        echo "<p style='color:red'>✗ Admin user NOT found!</p>";
        echo "<h3>Solution: Create Admin User</h3>";
        echo "<p>Run this SQL:</p>";
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "<pre style='background:#f0f0f0;padding:1rem;'>
INSERT INTO users (username, email, password_hash, full_name, role, is_active)
VALUES ('admin', 'admin@fmchecks.local', '{$hash}', 'System Administrator', 'admin', 1);
</pre>";

        echo "<p>Or click here to auto-create:</p>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='create_admin' class='btn btn-primary'>Create Admin User</button>";
        echo "</form>";

        if (isset($_POST['create_admin'])) {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, full_name, role, is_active)
                VALUES ('admin', 'admin@fmchecks.local', ?, 'System Administrator', 'admin', 1)
            ");
            $stmt->execute([$hash]);
            echo "<p style='color:green;font-weight:bold'>✓ Admin user created! Refresh this page.</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='test-login-full.php'>Back to Login Test</a></p>";
?>
<style>
body { font-family: sans-serif; padding: 2rem; max-width: 800px; margin: 0 auto; }
.btn { padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; }
</style>
