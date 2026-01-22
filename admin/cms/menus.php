<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$title = "Menu Manager";
$success = flash('success');
$error = flash('error');

// Handle Add Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu'])) {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    if (!empty($name) && !empty($location)) {
        $stmt = db()->prepare("INSERT INTO menus (name, location) VALUES (?, ?)");
        $stmt->execute([$name, $location]);
        $_SESSION['success'] = "Menu created.";
        redirect('menus.php');
    }
}

// Fetch Menus
$stmt = db()->query("SELECT * FROM menus ORDER BY name ASC");
$menus = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Menu Manager</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Menu Manager</li>
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
                        <h4>Create New Menu</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="add_menu" value="1">
                            <div class="mb-3">
                                <label class="form-label">Menu Name</label>
                                <input class="form-control" name="name" type="text" placeholder="e.g. Primary Navigation" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <select class="form-select" name="location">
                                    <option value="header">Header</option>
                                    <option value="footer">Footer</option>
                                    <option value="sidebar">Sidebar</option>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Create Menu</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h4>Active Menus</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo e($success); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menus as $menu): ?>
                                    <tr>
                                        <td><strong><?php echo e($menu['name']); ?></strong></td>
                                        <td><code><?php echo e($menu['location']); ?></code></td>
                                        <td>
                                            <a href="menu-edit.php?id=<?php echo $menu['id']; ?>" class="btn btn-xs btn-info">Manage Items</a>
                                            <a href="menus.php?delete=<?php echo $menu['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this menu?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
