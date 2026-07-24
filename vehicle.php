<?php
/**
 * AutoVault - Public vehicle details page
 *
 * Displays one available vehicle and its images. This milestone is read-only:
 * it does not add favourites, test-drive requests, or any other write action.
 */

require_once __DIR__ . '/includes/vehicle-functions.php';

$vehicle = null;
$images = [];
$pageError = null;
$vehicleId = null;

// Accept only a positive, base-10 integer that fits the schema's UNSIGNED INT.
$rawId = $_GET['id'] ?? null;
if (
    !is_string($rawId)
    || !preg_match('/^[1-9][0-9]*$/', $rawId)
    || strlen($rawId) > 10
    || (int) $rawId > 4294967295
) {
    http_response_code(400);
    $pageError = 'invalid';
} else {
    $vehicleId = (int) $rawId;

    try {
        require_once __DIR__ . '/includes/db.php';

        // The status condition makes unavailable records indistinguishable
        // from records that do not exist.
        $vehicleStmt = $pdo->prepare(
            "SELECT make, model, year, price, mileage, fuel_type,
                    transmission, body_type, color, doors, seats,
                    description, status
             FROM vehicles
             WHERE id = :id AND status = :status
             LIMIT 1"
        );
        $vehicleStmt->execute([
            ':id' => $vehicleId,
            ':status' => 'available',
        ]);
        $vehicle = $vehicleStmt->fetch();

        if (!$vehicle) {
            http_response_code(404);
            $pageError = 'not-found';
        } else {
            // Primary image first, followed by the remaining gallery images.
            $imageStmt = $pdo->prepare(
                "SELECT image_path, alt_text
                 FROM vehicle_images
                 WHERE vehicle_id = :vehicle_id
                 ORDER BY is_primary DESC, sort_order ASC, id ASC"
            );
            $imageStmt->execute([':vehicle_id' => $vehicleId]);

            // Discard unsafe paths before anything reaches an <img> element.
            foreach ($imageStmt->fetchAll() as $image) {
                $safeSrc = catalogue_image_src($image['image_path'] ?? null);
                if ($safeSrc !== null) {
                    $image['safe_src'] = $safeSrc;
                    $images[] = $image;
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Vehicle details query failed: ' . $e->getMessage());
        http_response_code(500);
        $pageError = 'database';
        $vehicle = null;
        $images = [];
    }
}

$vehicleName = $vehicle
    ? $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']
    : 'Vehicle Details';
$pageTitle = $vehicleName;
$metaDescription = $vehicle
    ? 'View details for the ' . $vehicleName . ' at AutoVault.'
    : 'View vehicle details at AutoVault.';

require __DIR__ . '/includes/header.php';
?>

    <section class="vehicle-details">
        <a class="vehicle-details__back" href="catalogue.php">&larr; Back to catalogue</a>

        <?php if ($pageError !== null): ?>
            <div class="vehicle-message" role="alert">
                <?php if ($pageError === 'invalid'): ?>
                    <h1>Invalid vehicle link</h1>
                    <p>The vehicle ID must be a positive whole number.</p>
                <?php elseif ($pageError === 'not-found'): ?>
                    <h1>Vehicle not found</h1>
                    <p>That vehicle is unavailable or does not exist.</p>
                <?php else: ?>
                    <h1>Vehicle details unavailable</h1>
                    <p>We could not load this vehicle right now. Please try again later.</p>
                <?php endif; ?>
                <a class="button" href="catalogue.php">Browse available vehicles</a>
            </div>
        <?php else: ?>
            <?php
                $defaultAlt = $vehicleName;
                $primaryImage = $images[0] ?? null;
                $additionalImages = array_slice($images, 1);
            ?>

            <header class="vehicle-details__header">
                <div>
                    <p class="vehicle-details__eyebrow">Available now</p>
                    <h1><?php echo e($vehicleName); ?></h1>
                </div>
                <p class="vehicle-details__price"><?php echo e(format_price($vehicle['price'])); ?></p>
            </header>

            <div class="vehicle-details__layout">
                <section class="vehicle-gallery" aria-label="Vehicle images">
                    <?php if ($primaryImage): ?>
                        <?php
                            $primaryAlt = trim((string) ($primaryImage['alt_text'] ?? ''));
                            if ($primaryAlt === '') {
                                $primaryAlt = $defaultAlt;
                            }
                        ?>
                        <img class="vehicle-gallery__primary"
                             src="<?php echo e($primaryImage['safe_src']); ?>"
                             alt="<?php echo e($primaryAlt); ?>">
                    <?php else: ?>
                        <div class="vehicle-gallery__placeholder" role="img"
                             aria-label="<?php echo e('No image available for ' . $defaultAlt); ?>">
                            No image available
                        </div>
                    <?php endif; ?>

                    <?php if ($additionalImages): ?>
                        <ul class="vehicle-gallery__list">
                            <?php foreach ($additionalImages as $index => $image): ?>
                                <?php
                                    $imageAlt = trim((string) ($image['alt_text'] ?? ''));
                                    if ($imageAlt === '') {
                                        $imageAlt = $defaultAlt . ' image ' . ($index + 2);
                                    }
                                ?>
                                <li>
                                    <img src="<?php echo e($image['safe_src']); ?>"
                                         alt="<?php echo e($imageAlt); ?>"
                                         loading="lazy">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <div class="vehicle-details__content">
                    <section class="vehicle-specs" aria-labelledby="specifications-heading">
                        <h2 id="specifications-heading">Key specifications</h2>
                        <dl class="vehicle-specs__grid">
                            <div>
                                <dt>Mileage</dt>
                                <dd><?php echo e(format_mileage($vehicle['mileage'])); ?></dd>
                            </div>
                            <div>
                                <dt>Transmission</dt>
                                <dd><?php echo e($vehicle['transmission']); ?></dd>
                            </div>
                            <div>
                                <dt>Fuel type</dt>
                                <dd><?php echo e($vehicle['fuel_type']); ?></dd>
                            </div>
                            <?php if (!empty($vehicle['body_type'])): ?>
                                <div>
                                    <dt>Body type</dt>
                                    <dd><?php echo e($vehicle['body_type']); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['color'])): ?>
                                <div>
                                    <dt>Colour</dt>
                                    <dd><?php echo e($vehicle['color']); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($vehicle['doors'] !== null): ?>
                                <div>
                                    <dt>Doors</dt>
                                    <dd><?php echo e($vehicle['doors']); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($vehicle['seats'] !== null): ?>
                                <div>
                                    <dt>Seats</dt>
                                    <dd><?php echo e($vehicle['seats']); ?></dd>
                                </div>
                            <?php endif; ?>
                            <div>
                                <dt>Status</dt>
                                <dd><?php echo e(ucfirst($vehicle['status'])); ?></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="vehicle-description" aria-labelledby="description-heading">
                        <h2 id="description-heading">Description</h2>
                        <?php if (trim((string) $vehicle['description']) !== ''): ?>
                            <p><?php echo nl2br(e($vehicle['description']), false); ?></p>
                        <?php else: ?>
                            <p>No description has been provided for this vehicle.</p>
                        <?php endif; ?>
                    </section>

                    <aside class="vehicle-actions" aria-label="Vehicle actions">
                        <h2>Interested in this vehicle?</h2>
                        <p>Favourites and test-drive booking will be available in a later milestone.</p>
                        <a class="button" href="catalogue.php">Continue browsing</a>
                    </aside>
                </div>
            </div>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
