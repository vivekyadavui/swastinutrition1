<?php
declare(strict_types=1);

/**
 * Installation script to set up database tables.
 */

// Include the database configuration directly for the installer
$dbConfig = require __DIR__ . '/cms/config/database.php';

echo "<h2>CMS Installation</h2>";
echo "<p>Target Database: <strong>{$dbConfig['name']}</strong> on <strong>{$dbConfig['host']}</strong></p>";

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
    
    echo "<p style='color: green;'>Connected to database successfully.</p>";

    // Read schema.sql
    $schemaPath = __DIR__ . '/cms/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found at $schemaPath");
    }
    
    $sql = file_get_contents($schemaPath);
    
    // Split SQL by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $pdo->beginTransaction();
    try {
        // Execute Schema
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
                if (preg_match('/CREATE TABLE (\w+)/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    echo "<p>Table created: <strong>$tableName</strong></p>";
                }
            }
        }

        // Execute Seed if admins table is empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            echo "<p>Seeding initial data...</p>";
            $seedPath = __DIR__ . '/cms/database/seed.sql';
            if (file_exists($seedPath)) {
                $seedSql = file_get_contents($seedPath);
                $seedStatements = array_filter(array_map('trim', explode(';', $seedSql)));
                foreach ($seedStatements as $sStatement) {
                    if (!empty($sStatement)) {
                        $pdo->exec($sStatement);
                    }
                }
                echo "<p style='color: green;'>Initial admin user created successfully.</p>";
            } else {
                echo "<p style='color: orange;'>Seed file not found, creating default admin manually...</p>";
                $hash = password_hash('admin123', PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO admins (name, email, password_hash, role, status) VALUES ('Admin', 'admin@example.com', ?, 'admin', 'active')")
                    ->execute([$hash]);
                echo "<p style='color: green;'>Default admin user created manually.</p>";
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    echo "<h3 style='color: green;'>Installation completed successfully!</h3>";
    echo "<p><a href='cms/index.php'>Go to Admin Login</a></p>";
    echo "<p style='color: red;'><strong>Important:</strong> Delete this file (install.php) after successful installation for security.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
