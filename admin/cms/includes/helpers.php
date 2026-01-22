<?php
declare(strict_types=1);

function debug_log(string $message, array $data = [], string $hypothesisId = 'Unknown'): void
{
    $logPath = __DIR__ . '/../debug.log';
    $logEntry = json_encode([
        'id' => uniqid('log_', true),
        'timestamp' => (int) (microtime(true) * 1000),
        'location' => $_SERVER['PHP_SELF'] ?? 'unknown',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => $hypothesisId
    ]);
    file_put_contents($logPath, $logEntry . PHP_EOL, FILE_APPEND);
}

function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
