<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$id = (int)($_GET['id'] ?? 0);

if (!$id) redirect('pages.php');

$stmt = db()->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if (!$page) redirect('pages.php');

$title = "Edit Page: " . $page['title'];
$errors = [];
$success = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
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

        // Check slug uniqueness excluding self
        $stmt = db()->prepare("SELECT id FROM pages WHERE slug = ? AND id != ? LIMIT 1");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $errors[] = "Slug already exists. Please use a unique slug.";
        }

        if (empty($errors)) {
            $stmt = db()->prepare("UPDATE pages SET title = ?, slug = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, canonical_url = ?, status = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$page_title, $slug, $meta_title, $meta_description, $meta_keywords, $canonical_url, $status, $id])) {
                $_SESSION['success'] = "Page updated successfully.";
                redirect("page-edit.php?id=$id");
            } else {
                $errors[] = "Failed to update page.";
            }
        }
    }
}

// Fetch Sections
$stmt = db()->prepare("SELECT * FROM page_sections WHERE page_id = ? ORDER BY sort_order ASC");
$stmt->execute([$id]);
$sections = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Edit Page: <?php echo e($page['title']); ?></h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item"><a href="pages.php">Page Manager</a></li>
                        <li class="breadcrumb-item active">Edit Page</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<div>".e($e)."</div>"; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="content-tab" data-bs-toggle="tab" href="#content" role="tab" aria-controls="content" aria-selected="true">Content & Sections</a></li>
                            <li class="nav-item"><a class="nav-link" id="seo-tab" data-bs-toggle="tab" href="#seo" role="tab" aria-controls="seo" aria-selected="false">SEO Settings</a></li>
                            <li class="nav-item"><a class="nav-link" id="settings-tab" data-bs-toggle="tab" href="#settings" role="tab" aria-controls="settings" aria-selected="false">Page Info</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <!-- Content & Sections Tab -->
                            <div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5>Dynamic Sections</h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">Add Section</button>
                                </div>

                                <div class="section-list">
                                    <?php if (empty($sections)): ?>
                                        <div class="text-center py-5 border rounded bg-light">
                                            <p class="text-muted">No sections added yet. Sections allow you to build complex page layouts.</p>
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">Create First Section</button>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($sections as $section): ?>
                                            <div class="card border mb-3">
                                                <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                                                    <div>
                                                        <strong>[<?php echo strtoupper($section['type']); ?>]</strong> 
                                                        <?php echo e($section['title'] ?: 'Untitled Section'); ?>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-<?php echo $section['status'] === 'active' ? 'success' : 'secondary'; ?> me-2">
                                                            <?php echo ucfirst($section['status']); ?>
                                                        </span>
                                                        <a href="section-edit.php?id=<?php echo $section['id']; ?>" class="btn btn-xs btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                                        <button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="update_page" value="1">
                                    <!-- Hidden fields for settings tab values to keep them intact -->
                                    <input type="hidden" name="title" value="<?php echo e($page['title']); ?>">
                                    <input type="hidden" name="slug" value="<?php echo e($page['slug']); ?>">
                                    <input type="hidden" name="status" value="<?php echo e($page['status']); ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input class="form-control" name="meta_title" type="text" value="<?php echo e($page['meta_title']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meta Keywords</label>
                                                <input class="form-control" name="meta_keywords" type="text" value="<?php echo e($page['meta_keywords']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Canonical URL</label>
                                                <input class="form-control" name="canonical_url" type="url" value="<?php echo e($page['canonical_url']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea class="form-control" name="meta_description" rows="5"><?php echo e($page['meta_description']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary" type="submit">Save SEO Settings</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Page Info Tab -->
                            <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="update_page" value="1">
                                    <!-- Hidden fields for SEO tab values -->
                                    <input type="hidden" name="meta_title" value="<?php echo e($page['meta_title']); ?>">
                                    <input type="hidden" name="meta_description" value="<?php echo e($page['meta_description']); ?>">
                                    <input type="hidden" name="meta_keywords" value="<?php echo e($page['meta_keywords']); ?>">
                                    <input type="hidden" name="canonical_url" value="<?php echo e($page['canonical_url']); ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Page Title</label>
                                        <input class="form-control" name="title" type="text" value="<?php echo e($page['title']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">URL Slug</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo e($config['base_url']); ?></span>
                                            <input class="form-control" name="slug" type="text" value="<?php echo e($page['slug']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="draft" <?php echo $page['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo $page['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary" type="submit">Update Page Info</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog" aria-labelledby="addSectionModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Section</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="section-add.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="page_id" value="<?php echo $id; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section Type</label>
                        <select class="form-select" name="type" required>
                            <option value="banner">Banner / Hero Section</option>
                            <option value="content">Rich Text Content</option>
                            <option value="gallery">Image Gallery</option>
                            <option value="faq">FAQ Accordion</option>
                            <option value="testimonials">Testimonials</option>
                            <option value="cta">Call to Action (CTA)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section Title (Internal)</label>
                        <input class="form-control" name="title" type="text" placeholder="e.g. Home Hero Banner">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
