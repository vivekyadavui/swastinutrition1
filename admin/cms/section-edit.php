<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('pages.php');

$stmt = db()->prepare("SELECT * FROM page_sections WHERE id = ?");
$stmt->execute([$id]);
$section = $stmt->fetch();

if (!$section) redirect('pages.php');

$title = "Edit Section: " . ucfirst($section['type']);
$success = flash('success');
$errors = [];

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_section'])) {
        $sec_title = trim($_POST['title'] ?? '');
        $sec_status = $_POST['status'] ?? 'active';
        $sec_content = $_POST['content'] ?? null;

        $stmt = db()->prepare("UPDATE page_sections SET title = ?, status = ?, content = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$sec_title, $sec_status, $sec_content, $id]);
        
        // Handle items if repeatable
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item_id => $item_data) {
                if (strpos((string)$item_id, 'new_') === 0) {
                    // New item
                    if (!empty($item_data['title']) || !empty($item_data['image_url'])) {
                        $stmt = db()->prepare("INSERT INTO section_items (section_id, title, subtitle, content, image_url, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $id, 
                            $item_data['title'] ?? '', 
                            $item_data['subtitle'] ?? '', 
                            $item_data['content'] ?? '', 
                            $item_data['image_url'] ?? '', 
                            $item_data['link_url'] ?? '', 
                            (int)($item_data['sort_order'] ?? 0)
                        ]);
                    }
                } else {
                    // Update existing
                    $stmt = db()->prepare("UPDATE section_items SET title = ?, subtitle = ?, content = ?, image_url = ?, link_url = ?, sort_order = ? WHERE id = ? AND section_id = ?");
                    $stmt->execute([
                        $item_data['title'] ?? '', 
                        $item_data['subtitle'] ?? '', 
                        $item_data['content'] ?? '', 
                        $item_data['image_url'] ?? '', 
                        $item_data['link_url'] ?? '', 
                        (int)($item_data['sort_order'] ?? 0),
                        $item_id,
                        $id
                    ]);
                }
            }
        }

        // Handle deletions
        if (isset($_POST['delete_items']) && is_array($_POST['delete_items'])) {
            foreach ($_POST['delete_items'] as $del_id) {
                $stmt = db()->prepare("DELETE FROM section_items WHERE id = ? AND section_id = ?");
                $stmt->execute([(int)$del_id, $id]);
            }
        }

        $_SESSION['success'] = "Section updated successfully.";
        redirect("section-edit.php?id=$id");
    }
}

// Fetch items
$stmt = db()->prepare("SELECT * FROM section_items WHERE section_id = ? ORDER BY sort_order ASC");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Edit <?php echo ucfirst($section['type']); ?> Section</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item"><a href="pages.php">Page Manager</a></li>
                        <li class="breadcrumb-item"><a href="page-edit.php?id=<?php echo $section['page_id']; ?>">Edit Page</a></li>
                        <li class="breadcrumb-item active">Edit Section</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <form method="post" id="sectionForm">
            <input type="hidden" name="update_section" value="1">
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header pb-0 d-flex justify-content-between">
                            <h4>Section Content</h4>
                            <?php if (in_array($section['type'], ['gallery', 'faq', 'testimonials'])): ?>
                                <button type="button" class="btn btn-primary btn-sm" id="addItemBtn">Add Item</button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Internal Title</label>
                                <input class="form-control" name="title" type="text" value="<?php echo e($section['title']); ?>" placeholder="e.g. Home Banner">
                            </div>

                            <?php if ($section['type'] === 'content'): ?>
                                <div class="mb-3">
                                    <label class="form-label">Body Content</label>
                                    <textarea class="form-control" name="content" id="summernote" rows="10"><?php echo e($section['content'] ?? ''); ?></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if (in_array($section['type'], ['banner'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">Banner Text / HTML</label>
                                    <textarea class="form-control" name="content" rows="4"><?php echo e($section['content'] ?? ''); ?></textarea>
                                </div>
                            <?php endif; ?>

                            <!-- Repeatable Items -->
                            <div id="itemsContainer">
                                <?php foreach ($items as $index => $item): ?>
                                    <div class="item-block card border mb-3" data-id="<?php echo $item['id']; ?>">
                                        <div class="card-header py-2 bg-light d-flex justify-content-between">
                                            <span>Item #<?php echo $index + 1; ?></span>
                                            <button type="button" class="btn btn-xs btn-danger remove-item">Remove</button>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Title</label>
                                                    <input class="form-control" name="items[<?php echo $item['id']; ?>][title]" value="<?php echo e($item['title']); ?>">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="form-label">Subtitle / Alt Text</label>
                                                    <input class="form-control" name="items[<?php echo $item['id']; ?>][subtitle]" value="<?php echo e($item['subtitle']); ?>">
                                                </div>
                                                <div class="col-md-12 mb-2">
                                                    <label class="form-label">Image URL / Icon Class</label>
                                                    <input class="form-control" name="items[<?php echo $item['id']; ?>][image_url]" value="<?php echo e($item['image_url']); ?>">
                                                </div>
                                                <div class="col-md-12 mb-2">
                                                    <label class="form-label">Content / Description</label>
                                                    <textarea class="form-control" name="items[<?php echo $item['id']; ?>][content]" rows="2"><?php echo e($item['content']); ?></textarea>
                                                </div>
                                                <div class="col-md-9 mb-2">
                                                    <label class="form-label">Link URL</label>
                                                    <input class="form-control" name="items[<?php echo $item['id']; ?>][link_url]" value="<?php echo e($item['link_url']); ?>">
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label class="form-label">Order</label>
                                                    <input class="form-control" name="items[<?php echo $item['id']; ?>][sort_order]" type="number" value="<?php echo $item['sort_order']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo $section['status'] === 'active' ? 'selected' : ''; ?>>Active / Enabled</option>
                                    <option value="disabled" <?php echo $section['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled / Hidden</option>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Save Section</button>
                            <a href="page-edit.php?id=<?php echo $section['page_id']; ?>" class="btn btn-light w-100 mt-2">Back to Page</a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="deleteContainer"></div>
        </form>
    </div>
</div>

<template id="itemTemplate">
    <div class="item-block card border mb-3" data-id="new_{TIMESTAMP}">
        <div class="card-header py-2 bg-light d-flex justify-content-between">
            <span>New Item</span>
            <button type="button" class="btn btn-xs btn-danger remove-item">Remove</button>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="items[new_{TIMESTAMP}][title]">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Subtitle / Alt Text</label>
                    <input class="form-control" name="items[new_{TIMESTAMP}][subtitle]">
                </div>
                <div class="col-md-12 mb-2">
                    <label class="form-label">Image URL / Icon Class</label>
                    <input class="form-control" name="items[new_{TIMESTAMP}][image_url]">
                </div>
                <div class="col-md-12 mb-2">
                    <label class="form-label">Content / Description</label>
                    <textarea class="form-control" name="items[new_{TIMESTAMP}][content]" rows="2"></textarea>
                </div>
                <div class="col-md-9 mb-2">
                    <label class="form-label">Link URL</label>
                    <input class="form-control" name="items[new_{TIMESTAMP}][link_url]">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Order</label>
                    <input class="form-control" name="items[new_{TIMESTAMP}][sort_order]" type="number" value="0">
                </div>
            </div>
        </div>
    </div>
</template>

<?php
$extra_css = '<link rel="stylesheet" type="text/css" href="../assets/css/vendors/summernote.css">';
$extra_js = '<script src="../assets/js/editor/summernote/summernote.js"></script>
<script src="../assets/js/editor/summernote/summernote.custom.js"></script>
<script>
    $(document).ready(function() {
        if ($("#summernote").length) {
            $("#summernote").summernote({ height: 300 });
        }

        $("#addItemBtn").click(function() {
            let timestamp = new Date().getTime();
            let html = $("#itemTemplate").html().replace(/{TIMESTAMP}/g, timestamp);
            $("#itemsContainer").append(html);
        });

        $(document).on("click", ".remove-item", function() {
            let block = $(this).closest(".item-block");
            let id = block.data("id");
            if (!String(id).startsWith("new_")) {
                $("#deleteContainer").append("<input type=\'hidden\' name=\'delete_items[]\' value=\'"+id+"\'>");
            }
            block.remove();
        });
    });
</script>';
include __DIR__ . '/includes/footer.php';
?>
