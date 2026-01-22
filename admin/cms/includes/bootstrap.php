<?php
declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';

$config = require BASE_PATH . '/config/config.php';

date_default_timezone_set($config['timezone']);
session_name($config['session_name']);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require BASE_PATH . '/includes/helpers.php';
debug_log('Bootstrap started', ['BASE_PATH' => BASE_PATH], 'B');
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/includes/auth.php';
debug_log('Bootstrap completed', [], 'B');
