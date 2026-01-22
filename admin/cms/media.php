<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$title = "Media Library";
$success = flash('success');
$error = flash('error');

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired.";
        redirect('media.php');
    }
    $file = $_FILES['file'];
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        $uploadDir = BASE_PATH . '/../assets/images/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid('media_') . '.' . $ext;
        $target = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = db()->prepare("INSERT INTO media (filename, filepath, filetype, filesize) VALUES (?, ?, ?, ?)");
            $stmt->execute([$file['name'], 'assets/images/uploads/' . $filename, $file['type'], $file['size']]);
            $_SESSION['success'] = "File uploaded successfully.";
            redirect('media.php');
        } else {
            $_SESSION['error'] = "Failed to move uploaded file.";
        }
    } else {
        $_SESSION['error'] = "Invalid file type.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("SELECT filepath FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    if ($media) {
        $path = BASE_PATH . '/../' . $media['filepath'];
        if (file_exists($path)) unlink($path);
        db()->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        $_SESSION['success'] = "File deleted.";
    }
    redirect('media.php');
}

// Fetch Media
$stmt = db()->query("SELECT * FROM media ORDER BY created_at DESC");
$files = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Media Library</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Media</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4>Upload Files</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo e($success); ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo e($error); ?></div>
                        <?php endif; ?>

                        <form action="media.php" method="post" enctype="multipart/form-data" class="dropzone" id="mediaDropzone">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <div class="fallback">
                                <input name="file" type="file" multiple>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header pb-0">
                        <h4>Gallery</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($files as $file): ?>
                            <div class="col-xl-2 col-md-3 col-sm-6">
                                <div class="card border h-100">
                                    <img src="../<?php echo e($file['filepath']); ?>" class="card-img-top p-2" style="height: 120px; object-fit: contain;">
                                    <div class="card-body p-2 border-top">
                                        <div class="text-truncate small mb-1" title="<?php echo e($file['filename']); ?>"><?php echo e($file['filename']); ?></div>
                                        <div class="d-flex justify-content-between">
                                            <button class="btn btn-xs btn-outline-info copy-path" data-path="<?php echo e($file['filepath']); ?>"><i class="fa fa-copy"></i></button>
                                            <a href="media.php?delete=<?php echo $file['id']; ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this file?')"><i class="fa fa-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_css = '<link rel="stylesheet" type="text/css" href="../assets/css/vendors/dropzone.css">';
$extra_js = '<script src="../assets/js/dropzone/dropzone.js"></script>
<script>
    $(document).ready(function() {
        $(".copy-path").click(function() {
            let path = $(this).data("path");
            navigator.clipboard.writeText(path);
            alert("Path copied to clipboard: " + path);
        });
    });
</script>';
include __DIR__ . '/includes/footer.php';
?>
