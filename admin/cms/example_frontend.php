<?php
declare(strict_types=1);

/**
 * Example Frontend Rendering Logic
 * This file demonstrates how to load and display a page on the website frontend.
 */

// 1. Setup Environment
require __DIR__ . '/includes/bootstrap.php';

// 2. Get Slug from URL (e.g. yoursite.com/about-us)
$slug = $_GET['slug'] ?? 'home';

// 3. Fetch Page with SEO Data
$stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    die("<h1>404 - Page Not Found</h1>");
}

// 4. Fetch Global Settings
$stmt = db()->query("SELECT setting_key, setting_value FROM settings");
$settings_rows = $stmt->fetchAll();
$site_settings = [];
foreach ($settings_rows as $row) {
    $site_settings[$row['setting_key']] = $row['setting_value'];
}

// 5. Fetch Page Sections
$stmt = db()->prepare("SELECT * FROM page_sections WHERE page_id = ? AND status = 'active' ORDER BY sort_order ASC");
$stmt->execute([$page['id']]);
$sections = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO SYSTEM -->
    <title><?php echo e($page['meta_title'] ?: ($page['title'] . ' | ' . $site_settings['site_name'])); ?></title>
    <meta name="description" content="<?php echo e($page['meta_description'] ?: $site_settings['meta_description']); ?>">
    <meta name="keywords" content="<?php echo e($page['meta_keywords'] ?: $site_settings['meta_keywords']); ?>">
    <?php if($page['canonical_url']): ?>
        <link rel="canonical" href="<?php echo e($page['canonical_url']); ?>">
    <?php endif; ?>

    <!-- OPEN GRAPH / SOCIAL -->
    <meta property="og:title" content="<?php echo e($page['og_title'] ?: $page['title']); ?>">
    <meta property="og:description" content="<?php echo e($page['og_description'] ?: $page['meta_description']); ?>">
    <meta property="og:image" content="<?php echo e($page['og_image']); ?>">
    <meta name="twitter:card" content="summary_large_image">

    <!-- JSON-LD SCHEMA -->
    <?php if($page['schema_json']): ?>
        <script type="application/ld+json"><?php echo $page['schema_json']; ?></script>
    <?php endif; ?>

    <link rel="stylesheet" href="path/to/your/frontend/style.css">
</head>
<body>

    <header>
        <!-- Use Menu Manager here -->
        <nav>
            <?php 
            // Example: Load Header Menu
            $stmt = db()->prepare("SELECT mi.*, p.slug FROM menu_items mi LEFT JOIN pages p ON mi.page_id = p.id WHERE mi.menu_id = (SELECT id FROM menus WHERE location = 'header' LIMIT 1) ORDER BY mi.sort_order ASC");
            $stmt->execute();
            $menu_items = $stmt->fetchAll();
            foreach($menu_items as $item): 
                $url = ($item['link_type'] === 'page') ? $item['slug'] : $item['custom_url'];
            ?>
                <a href="<?php echo e($url); ?>"><?php echo e($item['title']); ?></a>
            <?php endforeach; ?>
        </nav>
    </header>

    <main>
        <?php foreach ($sections as $section): ?>
            <section class="section-<?php echo e($section['type']); ?>" id="section-<?php echo $section['id']; ?>">
                
                <?php if ($section['type'] === 'banner'): ?>
                    <div class="hero">
                        <div class="container">
                            <?php echo $section['content']; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($section['type'] === 'content'): ?>
                    <div class="container rich-text">
                        <?php echo $section['content']; ?>
                    </div>
                <?php endif; ?>

                <?php if ($section['type'] === 'gallery'): ?>
                    <div class="gallery-grid">
                        <?php 
                        $stmt = db()->prepare("SELECT * FROM section_items WHERE section_id = ? ORDER BY sort_order ASC");
                        $stmt->execute([$section['id']]);
                        $items = $stmt->fetchAll();
                        foreach($items as $item):
                        ?>
                            <div class="gallery-item">
                                <img src="<?php echo e($item['image_url']); ?>" alt="<?php echo e($item['subtitle']); ?>">
                                <p><?php echo e($item['title']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add more section types (FAQ, Testimonials, etc.) as needed -->

            </section>
        <?php endforeach; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo e($site_settings['site_name']); ?></p>
    </footer>

</body>
</html>
