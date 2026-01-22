<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$title = "Global Settings";
$success = flash('success');

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    $_SESSION['success'] = "Settings updated successfully.";
    redirect('settings.php');
}

// Fetch Settings
$stmt = db()->query("SELECT setting_key, setting_value FROM settings");
$rows = $stmt->fetchAll();
$settings = [];
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Global Settings</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>General Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input class="form-control" name="settings[site_name]" value="<?php echo e($settings['site_name'] ?? 'Swasti Nutrition'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Support Email</label>
                                <input class="form-control" name="settings[contact_email]" value="<?php echo e($settings['contact_email'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input class="form-control" name="settings[contact_phone]" value="<?php echo e($settings['contact_phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="settings[contact_address]"><?php echo e($settings['contact_address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>Global SEO</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Default Meta Title</label>
                                <input class="form-control" name="settings[meta_title]" value="<?php echo e($settings['meta_title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default Meta Description</label>
                                <textarea class="form-control" name="settings[meta_description]"><?php echo e($settings['meta_description'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default Meta Keywords</label>
                                <input class="form-control" name="settings[meta_keywords]" value="<?php echo e($settings['meta_keywords'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4>Social Links</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Facebook</label>
                                <input class="form-control" name="settings[social_facebook]" value="<?php echo e($settings['social_facebook'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instagram</label>
                                <input class="form-control" name="settings[social_instagram]" value="<?php echo e($settings['social_instagram'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Twitter</label>
                                <input class="form-control" name="settings[social_twitter]" value="<?php echo e($settings['social_twitter'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">LinkedIn</label>
                                <input class="form-control" name="settings[social_linkedin]" value="<?php echo e($settings['social_linkedin'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <button class="btn btn-primary w-100" type="submit">Save All Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
