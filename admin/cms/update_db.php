<?php
declare(strict_types=1);

/**
 * Update Database Schema Script (CMS Folder Fallback)
 */

require __DIR__ . '/includes/bootstrap.php';

echo "<h2>Database Schema Update</h2>";

try {
    $pdo = db();
    echo "<p style='color: green;'>Connected to database successfully.</p>";

    // Read schema.sql
    $schemaPath = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found at $schemaPath");
    }
    
    $sql = file_get_contents($schemaPath);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                if (preg_match('/CREATE TABLE (\w+)/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    echo "Processing table: <strong>$tableName</strong>... ";
                    
                    $check = $pdo->query("SHOW TABLES LIKE '$tableName'")->fetch();
                    if ($check) {
                        echo "Already exists. Skipping CREATE.";
                    } else {
                        $pdo->exec($statement);
                        echo "<span style='color: green;'>Created!</span>";
                    }
                } else {
                    $pdo->exec($statement);
                }
            } catch (Exception $e) {
                // Ignore errors for existing columns
                if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                    echo "<span style='color: orange;'>Note: " . $e->getMessage() . "</span>";
                }
            }
            echo "<br>";
        }
    }

    // Manual migration for existing 'pages' table to add new columns
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
    echo "<p><a href='index.php'>Go to Dashboard</a></p>";
    echo "<p style='color: red;'><strong>Important:</strong> Delete this file (update_db.php) after use.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . $e->getMessage() . "</p>";
}
