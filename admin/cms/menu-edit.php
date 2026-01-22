<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('menus.php');

$stmt = db()->prepare("SELECT * FROM menus WHERE id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch();
if (!$menu) redirect('menus.php');

$title = "Manage Menu: " . $menu['name'];
$success = flash('success');

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $type = $_POST['link_type'] ?? 'page';
        $page_id = (int)($_POST['page_id'] ?? 0) ?: null;
        $custom_url = trim($_POST['custom_url'] ?? '');

        $stmt = db()->prepare("INSERT INTO menu_items (menu_id, title, link_type, page_id, custom_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $title, $type, $page_id, $custom_url]);
        $_SESSION['success'] = "Menu item added.";
        redirect("menu-edit.php?id=$id");
    }
}

// Fetch Pages for linking
$stmt = db()->query("SELECT id, title FROM pages ORDER BY title ASC");
$pages = $stmt->fetchAll();

// Fetch Menu Items
$stmt = db()->prepare("SELECT mi.*, p.title as page_title FROM menu_items mi LEFT JOIN pages p ON mi.page_id = p.id WHERE mi.menu_id = ? ORDER BY mi.sort_order ASC");
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
                    <h3>Manage Menu: <?php echo e($menu['name']); ?></h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item"><a href="menus.php">Menu Manager</a></li>
                        <li class="breadcrumb-item active">Edit Menu</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h4>Add Menu Item</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="add_item" value="1">
                            <div class="mb-3">
                                <label class="form-label">Label</label>
                                <input class="form-control" name="title" type="text" placeholder="e.g. About Us" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link Type</label>
                                <select class="form-select" name="link_type" id="link_type">
                                    <option value="page">Page</option>
                                    <option value="custom">Custom URL</option>
                                </select>
                            </div>
                            <div class="mb-3" id="page_select">
                                <label class="form-label">Select Page</label>
                                <select class="form-select" name="page_id">
                                    <?php foreach ($pages as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo e($p['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3 d-none" id="custom_url_input">
                                <label class="form-label">Custom URL</label>
                                <input class="form-control" name="custom_url" type="text" placeholder="https://...">
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Add to Menu</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h4>Menu Structure</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo e($success); ?></div>
                        <?php endif; ?>

                        <div class="dd" id="menu-nestable">
                            <ol class="dd-list list-group">
                                <?php foreach ($items as $item): ?>
                                <li class="dd-item list-group-item d-flex justify-content-between align-items-center" data-id="<?php echo $item['id']; ?>">
                                    <div class="dd-handle"><i class="fa fa-bars me-2"></i> <?php echo e($item['title']); ?></div>
                                    <div class="small text-muted">
                                        <?php if ($item['link_type'] === 'page'): ?>
                                            Page: <?php echo e($item['page_title']); ?>
                                        <?php else: ?>
                                            URL: <?php echo e($item['custom_url']); ?>
                                        <?php endif; ?>
                                        <a href="menu-item-delete.php?id=<?php echo $item['id']; ?>&menu_id=<?php echo $id; ?>" class="ms-3 text-danger"><i class="fa fa-trash"></i></a>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '<script>
    $(document).ready(function() {
        $("#link_type").change(function() {
            if ($(this).val() === "custom") {
                $("#custom_url_input").removeClass("d-none");
                $("#page_select").addClass("d-none");
            } else {
                $("#custom_url_input").addClass("d-none");
                $("#page_select").removeClass("d-none");
            }
        });
    });
</script>';
include __DIR__ . '/includes/footer.php';
?>
