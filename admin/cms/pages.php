<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$title = "Page Manager";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM pages WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = "Page deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete page.";
    }
    redirect('pages.php');
}

// Fetch Pages
$stmt = db()->query("SELECT id, title, slug, status, updated_at FROM pages ORDER BY created_at DESC");
$pages = $stmt->fetchAll();

$success = flash('success');
$error = flash('error');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Page Manager</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Page Manager</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4>All Pages</h4>
                        <a href="page-add.php" class="btn btn-primary">Add New Page</a>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo e($success); ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo e($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="table-responsive custom-scrollbar">
                            <table class="display" id="basic-1">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pages as $page): ?>
                                    <tr>
                                        <td><?php echo e((string)$page['id']); ?></td>
                                        <td><strong><?php echo e($page['title']); ?></strong></td>
                                        <td><code>/<?php echo e($page['slug']); ?></code></td>
                                        <td>
                                            <span class="badge badge-<?php echo $page['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($page['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y, H:i', strtotime($page['updated_at'])); ?></td>
                                        <td>
                                            <ul class="action">
                                                <li class="edit"> <a href="page-edit.php?id=<?php echo $page['id']; ?>"><i class="icon-pencil-alt"></i></a></li>
                                                <li class="delete"><a href="pages.php?delete=<?php echo $page['id']; ?>" onclick="return confirm('Are you sure you want to delete this page?')"><i class="icon-trash"></i></a></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($pages)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No pages found. <a href="page-add.php">Create your first page.</a></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->
</div>

<?php
$extra_js = '<script src="../assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
<script>$(document).ready(function() { $("#basic-1").DataTable(); });</script>';
include __DIR__ . '/includes/footer.php';
?>
