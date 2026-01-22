<?php
declare(strict_types=1);

/**
 * Force Reseed Script
 * Use this if the initial admin user was not created.
 */

$dbConfig = require __DIR__ . '/cms/config/database.php';

echo "<h2>Admin User Reseed</h2>";

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['name'],
        $dbConfig['charset']
    );

    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "<p>Connected to database successfully.</p>";

    // Check if table exists
    try {
        $pdo->query("SELECT 1 FROM admins LIMIT 1");
    } catch (Exception $e) {
        echo "<p style='color: red;'>Table 'admins' does not exist. Please run install.php first.</p>";
        exit;
    }

    $email = 'admin@example.com';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $name = 'Admin User';

    // Delete existing admin if any with same email to avoid unique constraint error
    $pdo->prepare("DELETE FROM admins WHERE email = ?")->execute([$email]);

    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
    $stmt->execute([$name, $email, $hash]);

    echo "<h3 style='color: green;'>Admin user created successfully!</h3>";
    echo "<ul>
            <li><strong>Email:</strong> $email</li>
            <li><strong>Password:</strong> $password</li>
          </ul>";
    echo "<p><a href='cms/login.php'>Go to Login Page</a></p>";
    echo "<p style='color: red;'><strong>Important:</strong> Delete this file (reseed.php) after use.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
