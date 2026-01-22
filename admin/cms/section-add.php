<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired.";
        redirect('pages.php');
    }
    $page_id = (int)$_POST['page_id'];
    $type = trim($_POST['type']);
    $title = trim($_POST['title'] ?? '');

    if ($page_id > 0 && !empty($type)) {
        // Get max sort order
        $stmt = db()->prepare("SELECT MAX(sort_order) FROM page_sections WHERE page_id = ?");
        $stmt->execute([$page_id]);
        $max_order = (int)$stmt->fetchColumn();

        $stmt = db()->prepare("INSERT INTO page_sections (page_id, type, title, sort_order, status) VALUES (?, ?, ?, ?, 'active')");
        if ($stmt->execute([$page_id, $type, $title, $max_order + 1])) {
            $section_id = db()->lastInsertId();
            $_SESSION['success'] = "Section added successfully.";
            redirect("section-edit.php?id=$section_id");
        }
    }
}

redirect('pages.php');
