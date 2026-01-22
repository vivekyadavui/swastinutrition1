<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$title = "Add New Page";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = "Security token expired. Please try again.";
    } else {
        $page_title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        $canonical_url = trim($_POST['canonical_url'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        if (empty($page_title)) $errors[] = "Title is required.";
        if (empty($slug)) $errors[] = "Slug is required.";

        // Check slug uniqueness
        $stmt = db()->prepare("SELECT id FROM pages WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $errors[] = "Slug already exists. Please use a unique slug.";
        }

        if (empty($errors)) {
            $stmt = db()->prepare("INSERT INTO pages (title, slug, meta_title, meta_description, meta_keywords, canonical_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$page_title, $slug, $meta_title, $meta_description, $meta_keywords, $canonical_url, $status])) {
                $page_id = db()->lastInsertId();
                $_SESSION['success'] = "Page created successfully.";
                redirect("page-edit.php?id=$page_id");
            } else {
                $errors[] = "Failed to create page.";
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Add New Page</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item"><a href="pages.php">Page Manager</a></li>
                        <li class="breadcrumb-item active">Add Page</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form class="form-bookmark needs-validation" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>Basic Information</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($errors): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $e) echo "<div>".e($e)."</div>"; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label" for="title">Page Title</label>
                                <input class="form-control" id="title" name="title" type="text" placeholder="Enter page title" required value="<?php echo e($_POST['title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="slug">URL Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo e($config['base_url']); ?></span>
                                    <input class="form-control" id="slug" name="slug" type="text" placeholder="page-url-slug" required value="<?php echo e($_POST['slug'] ?? ''); ?>">
                                </div>
                                <small class="text-muted">Slug will be used in the URL. Only lowercase letters, numbers, and dashes.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>Publishing</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="draft" <?php echo ($_POST['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Create Page</button>
                            <a href="pages.php" class="btn btn-light w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>SEO Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Title</label>
                                        <input class="form-control" name="meta_title" type="text" placeholder="SEO Title" value="<?php echo e($_POST['meta_title'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Meta Keywords</label>
                                        <input class="form-control" name="meta_keywords" type="text" placeholder="keyword1, keyword2" value="<?php echo e($_POST['meta_keywords'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Canonical URL</label>
                                        <input class="form-control" name="canonical_url" type="url" placeholder="https://example.com/page" value="<?php echo e($_POST['canonical_url'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Description</label>
                                        <textarea class="form-control" name="meta_description" rows="5" placeholder="Enter SEO description"><?php echo e($_POST['meta_description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
