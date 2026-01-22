<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$token = $_GET['token'] ?? null;

if ($id <= 0 || !verify_csrf($token)) {
    flash('error', 'Invalid delete request.');
    redirect('pages.php');
}

$stmt = db()->prepare('DELETE FROM pages WHERE id = :id');
$stmt->execute(['id' => $id]);

flash('success', 'Page deleted successfully.');
redirect('pages.php');
