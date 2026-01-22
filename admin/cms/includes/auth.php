<?php
declare(strict_types=1);

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    if (!empty($_SESSION['admin_name']) && !empty($_SESSION['admin_email'])) {
        return [
            'id' => (int) $_SESSION['admin_id'],
            'name' => (string) $_SESSION['admin_name'],
            'email' => (string) $_SESSION['admin_email'],
        ];
    }

    $stmt = db()->prepare('SELECT id, name, email FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['admin_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        logout_user();
        return null;
    }

    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_email'] = $user['email'];

    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $user['id'];
    $_SESSION['admin_name'] = (string) $user['name'];
    $_SESSION['admin_email'] = (string) $user['email'];
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
