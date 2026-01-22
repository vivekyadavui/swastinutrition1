<?php
declare(strict_types=1);

/**
 * Update Database Schema Script
 */

$dbConfig = require __DIR__ . '/cms/config/database.php';

echo "<h2>Database Schema Update</h2>";

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

    // Disable foreign key checks for dropping tables if needed (careful)
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Read schema.sql
    $schemaPath = __DIR__ . '/cms/database/schema.sql';
    $sql = file_get_contents($schemaPath);
    
    // Split SQL by semicolon and execute each statement
    // We use a more robust way to handle the DROP and CREATE statements
    // But for simplicity in this script, we'll try to execute each one.
    // NOTE: This might fail if tables already exist and don't have DROP TABLE IF EXISTS.
    // I will modify the schema.sql to include DROP TABLE IF EXISTS or handle it here.
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                // If it's a CREATE TABLE, we might want to skip if exists or drop it
                if (preg_match('/CREATE TABLE (\w+)/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    echo "Processing table: <strong>$tableName</strong>... ";
                    
                    // We can't easily "update" a table structure with just CREATE TABLE.
                    // For this step-by-step build, if the table exists, we'll skip it OR 
                    // if we want to BE SURE, we drop it (LOSES DATA).
                    // Let's try to add columns if they don't exist for 'pages' and 'settings'
                    // and CREATE new tables.
                    
                    $check = $pdo->query("SHOW TABLES LIKE '$tableName'")->fetch();
                    if ($check) {
                        echo "Already exists. Skipping CREATE. (Use migrations for structural changes)";
                    } else {
                        $pdo->exec($statement);
                        echo "<span style='color: green;'>Created!</span>";
                    }
                } else {
                    $pdo->exec($statement);
                }
            } catch (Exception $e) {
                echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span>";
            }
            echo "<br>";
        }
    }

    // Manual migration for existing 'pages' table to add new columns if they don't exist
    $columnsToAdd = [
        'meta_keywords' => 'VARCHAR(255) DEFAULT NULL AFTER meta_description',
        'canonical_url' => 'VARCHAR(255) DEFAULT NULL AFTER meta_keywords',
        'og_title' => 'VARCHAR(190) DEFAULT NULL AFTER canonical_url',
        'og_description' => 'VARCHAR(255) DEFAULT NULL AFTER og_title',
        'og_image' => 'VARCHAR(255) DEFAULT NULL AFTER og_description',
        'twitter_title' => 'VARCHAR(190) DEFAULT NULL AFTER og_image',
        'twitter_description' => 'VARCHAR(255) DEFAULT NULL AFTER twitter_title',
        'twitter_image' => 'VARCHAR(255) DEFAULT NULL AFTER twitter_description',
        'schema_json' => 'JSON DEFAULT NULL AFTER twitter_image'
    ];

    foreach ($columnsToAdd as $col => $def) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM pages LIKE '$col'")->fetch();
        if (!$checkCol) {
            $pdo->exec("ALTER TABLE pages ADD $col $def");
            echo "Added column <strong>$col</strong> to pages table.<br>";
        }
    }

    echo "<h3 style='color: green;'>Schema update completed!</h3>";
    echo "<p><a href='cms/pages.php'>Go to Page Manager</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . $e->getMessage() . "</p>";
}
