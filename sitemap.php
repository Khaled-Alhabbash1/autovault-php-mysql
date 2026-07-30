<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - HTML sitemap (public, dynamic)
 * -------------------------------------------------------------------------
 * Lists the main site sections and every available vehicle, so visitors and
 * search engines have one page linking to all public content. The vehicle
 * list is generated from the database with a prepared statement.
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vehicle-functions.php';

$vehicles = [];
$dbError = false;
try {
    // Only list vehicles that are publicly visible (available).
    $stmt = $pdo->prepare(
        "SELECT id, year, make, model
         FROM vehicles
         WHERE status = 'available'
         ORDER BY make ASC, model ASC, year DESC"
    );
    $stmt->execute();
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Sitemap query failed: ' . $e->getMessage());
    $dbError = true;
}

$pageTitle       = 'Sitemap';
$metaDescription = 'A complete map of AutoVault public pages and the current vehicle listings.';
require __DIR__ . '/includes/header.php';
?>

    <section class="page-intro">
        <h1>Sitemap</h1>
        <p>Every public area of AutoVault, plus links to each vehicle currently available.</p>
    </section>

    <section class="sitemap-sections" aria-labelledby="sitemap-main-heading">
        <h2 id="sitemap-main-heading">Main pages</h2>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="media.php">Media</a></li>
            <li><a href="about.php">About &amp; location map</a></li>
            <li><a href="help.php">Help centre</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="privacy.php">Privacy</a></li>
            <li><a href="accessibility.php">Accessibility</a></li>
        </ul>
    </section>

    <section class="sitemap-vehicles" aria-labelledby="sitemap-vehicles-heading">
        <h2 id="sitemap-vehicles-heading">Available vehicles</h2>
        <?php if ($dbError): ?>
            <p class="note">The vehicle list is unavailable right now. Please try again later.</p>
        <?php elseif (empty($vehicles)): ?>
            <p class="note">There are no available vehicles to list yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($vehicles as $v): ?>
                    <li>
                        <a href="vehicle.php?id=<?php echo (int) $v['id']; ?>">
                            <?php echo e($v['year'] . ' ' . $v['make'] . ' ' . $v['model']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

<?php
require __DIR__ . '/includes/footer.php';
