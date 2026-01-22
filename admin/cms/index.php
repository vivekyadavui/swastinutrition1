<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$title = "Dashboard";

// Fetch some stats for the dashboard
$pageCount = db()->query("SELECT COUNT(*) FROM pages")->fetchColumn();
$sectionCount = db()->query("SELECT COUNT(*) FROM page_sections")->fetchColumn();
$mediaCount = db()->query("SELECT COUNT(*) FROM media")->fetchColumn();

// Fetch recent pages
$stmt = db()->query("SELECT id, title, slug, updated_at FROM pages ORDER BY updated_at DESC LIMIT 5");
$recentPages = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Welcome back, <?php echo e($user['name']); ?>!</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Stats Widgets -->
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden border-0">
                    <div class="bg-primary b-r-4 card-body">
                        <div class="media static-top-widget">
                            <div class="align-self-center text-center"><i data-feather="file-text"></i></div>
                            <div class="media-body"><span class="m-0">Total Pages</span>
                                <h4 class="mb-0 counter"><?php echo $pageCount; ?></h4><i class="icon-bg" data-feather="file-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden border-0">
                    <div class="bg-secondary b-r-4 card-body">
                        <div class="media static-top-widget">
                            <div class="align-self-center text-center"><i data-feather="grid"></i></div>
                            <div class="media-body"><span class="m-0">Total Sections</span>
                                <h4 class="mb-0 counter"><?php echo $sectionCount; ?></h4><i class="icon-bg" data-feather="grid"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden border-0">
                    <div class="bg-warning b-r-4 card-body">
                        <div class="media static-top-widget">
                            <div class="align-self-center text-center"><i data-feather="image"></i></div>
                            <div class="media-body"><span class="m-0">Media Files</span>
                                <h4 class="mb-0 counter"><?php echo $mediaCount; ?></h4><i class="icon-bg" data-feather="image"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden border-0">
                    <div class="bg-info b-r-4 card-body">
                        <div class="media static-top-widget">
                            <div class="align-self-center text-center"><i data-feather="users"></i></div>
                            <div class="media-body"><span class="m-0">Admins</span>
                                <h4 class="mb-0 counter">1</h4><i class="icon-bg" data-feather="users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recently Updated Pages</h5>
                    </div>
                    <div class="card-body">
                        <div class="user-status table-responsive custom-scrollbar">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr>
                                        <th scope="col">Page Title</th>
                                        <th scope="col">Slug</th>
                                        <th scope="col">Last Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPages as $rp): ?>
                                    <tr>
                                        <td class="f-w-600"><?php echo e($rp['title']); ?></td>
                                        <td><code>/<?php echo e($rp['slug']); ?></code></td>
                                        <td><?php echo date('d M, H:i', strtotime($rp['updated_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($recentPages)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No pages created yet.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="page-add.php" class="btn btn-outline-primary w-100 py-3 text-center d-block">
                                    <i data-feather="plus-circle" class="mb-2 d-block mx-auto"></i> Add New Page
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="media.php" class="btn btn-outline-secondary w-100 py-3 text-center d-block">
                                    <i data-feather="upload" class="mb-2 d-block mx-auto"></i> Upload Media
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="settings.php" class="btn btn-outline-warning w-100 py-3 text-center d-block">
                                    <i data-feather="settings" class="mb-2 d-block mx-auto"></i> Site Settings
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="menus.php" class="btn btn-outline-info w-100 py-3 text-center d-block">
                                    <i data-feather="menu" class="mb-2 d-block mx-auto"></i> Manage Menus
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
