<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = require BASE_PATH . '/config/database.php';
    debug_log('Attempting DB connection', ['host' => $db['host'], 'name' => $db['name']], 'A');
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['name'],
        $db['charset']
    );

    try {
        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        debug_log('DB connection successful', [], 'A');
    } catch (PDOException $e) {
        debug_log('DB connection failed', ['error' => $e->getMessage()], 'A');
        throw $e;
    }

    return $pdo;
}
