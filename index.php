<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Home page
 * -------------------------------------------------------------------------
 * The landing page. It shows a short welcome and a small "Featured vehicles"
 * strip so visitors immediately see real vehicle photos. It is PUBLIC - no
 * login is required - and only reads data with prepared statements.
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/db.php';                // gives us $pdo
require_once __DIR__ . '/includes/vehicle-functions.php'; // image + format helpers

// Fetch up to three available vehicles (featured first) with their primary
// image. Static SQL, no user input, but still run as a prepared statement.
$featured = [];
try {
    $featuredStmt = $pdo->prepare(
        "SELECT v.id, v.make, v.model, v.year, v.price,
                pi.image_path, pi.alt_text
         FROM vehicles v
         LEFT JOIN vehicle_images pi ON pi.id = (
             SELECT id FROM vehicle_images
             WHERE vehicle_id = v.id
             ORDER BY is_primary DESC, sort_order ASC, id ASC
             LIMIT 1
         )
         WHERE v.status = 'available'
         ORDER BY v.is_featured DESC, v.created_at DESC, v.id DESC
         LIMIT 3"
    );
    $featuredStmt->execute();
    $featured = $featuredStmt->fetchAll();
} catch (PDOException $e) {
    // The home page still works without the strip; log the real reason only.
    error_log('Home featured vehicles query failed: ' . $e->getMessage());
    $featured = [];
}

// Set the page title and description, then load the shared header.
$pageTitle       = 'Home';
$metaDescription = 'Browse vehicles, save your favourites and request a test drive with AutoVault.';
require __DIR__ . '/includes/header.php';
?>

    <!-- Hero / welcome banner -->
    <section class="hero">
        <h1>Find your next vehicle with AutoVault</h1>
        <p>
            Browse a wide range of vehicles, save your favourites and request a
            test drive - all in one place.
        </p>
        <p>
            <a class="button" href="catalogue.php">Browse the catalogue</a>
        </p>
    </section>

    <?php if (!empty($featured)): ?>
        <!-- Featured vehicles: real photos linking through to each vehicle -->
        <section class="features">
            <h2>Featured vehicles</h2>
            <ul class="vehicle-grid">
                <?php foreach ($featured as $v): ?>
                    <?php
                        $vehicleId = (int) $v['id'];
                        // Only use the real photo when the file truly exists.
                        $imageSrc = catalogue_image_src($v['image_path'] ?? null);
                        if ($imageSrc !== null && !vehicle_image_exists($imageSrc)) {
                            $imageSrc = null;
                        }
                        $label = $v['year'] . ' ' . $v['make'] . ' ' . $v['model'];
                        $altText = trim((string) ($v['alt_text'] ?? '')) !== ''
                            ? $v['alt_text']
                            : $label;
                    ?>
                    <li class="vehicle-card">
                        <a class="vehicle-card__link" href="vehicle.php?id=<?php echo $vehicleId; ?>">
                            <?php if ($imageSrc !== null): ?>
                                <img class="vehicle-card__img"
                                     src="<?php echo e($imageSrc); ?>"
                                     alt="<?php echo e($altText); ?>"
                                     width="640" height="360" loading="lazy">
                            <?php else: ?>
                                <img class="vehicle-card__img"
                                     src="<?php echo e(vehicle_placeholder_src()); ?>"
                                     alt="<?php echo e('No photograph available for ' . $label); ?>"
                                     width="640" height="360" loading="lazy">
                            <?php endif; ?>
                            <div class="vehicle-card__body">
                                <h3 class="vehicle-card__title"><?php echo e($label); ?></h3>
                                <p class="vehicle-card__price"><?php echo e(format_price($v['price'])); ?></p>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Simple feature highlights (static content) -->
    <section class="features">
        <h2>Why AutoVault?</h2>
        <div class="feature-grid">
            <article class="feature-card">
                <h3>Wide selection</h3>
                <p>A growing catalogue of vehicles with clear details and photos.</p>
            </article>
            <article class="feature-card">
                <h3>Save favourites</h3>
                <p>Create an account to keep a list of the vehicles you love.</p>
            </article>
            <article class="feature-card">
                <h3>Request a test drive</h3>
                <p>Book a test drive online in just a few minutes.</p>
            </article>
        </div>
    </section>

<?php
// Load the shared footer to close the page.
require __DIR__ . '/includes/footer.php';
