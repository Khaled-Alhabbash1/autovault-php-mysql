<?php
/**
 * AutoVault - Public vehicle details page
 *
 * Displays one available vehicle and its images. Logged-in users can save or
 * remove it through the CSRF-protected POST handler in favourites.php.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/vehicle-functions.php';
require_once __DIR__ . '/includes/favourite-functions.php';

$vehicle = null;
$images = [];
$options = [];
$pageError = null;
$isFavourite = false;
$favouriteFlash = take_favourite_flash();

// Accept only a positive, base-10 integer that fits the schema's UNSIGNED INT.
$vehicleId = parse_vehicle_id($_GET['id'] ?? null);
if ($vehicleId === null) {
    http_response_code(400);
    $pageError = 'invalid';
} else {
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

            $optionStmt = $pdo->prepare(
                "SELECT id, option_group, option_name, price_adjustment, is_default
                 FROM vehicle_options
                 WHERE vehicle_id = :vehicle_id
                 ORDER BY sort_order ASC, id ASC"
            );
            $optionStmt->execute([':vehicle_id' => $vehicleId]);
            $options = $optionStmt->fetchAll();

            // Favourite ownership is always checked with the session user ID.
            if (is_logged_in()) {
                $favouriteStmt = $pdo->prepare(
                    "SELECT 1
                     FROM favourites
                     WHERE user_id = :user_id AND vehicle_id = :vehicle_id
                     LIMIT 1"
                );
                $favouriteStmt->execute([
                    ':user_id' => (int) current_user()['id'],
                    ':vehicle_id' => $vehicleId,
                ]);
                $isFavourite = (bool) $favouriteStmt->fetchColumn();
            }
        }
    } catch (PDOException $e) {
        error_log('Vehicle details query failed: ' . $e->getMessage());
        http_response_code(500);
        $pageError = 'database';
        $vehicle = null;
        $images = [];
        $options = [];
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
        <span class="context-help"><a href="help-catalogue.php">Vehicle details help</a></span>

        <?php if ($favouriteFlash !== null): ?>
            <div class="<?php echo $favouriteFlash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                 role="<?php echo $favouriteFlash['type'] === 'error' ? 'alert' : 'status'; ?>">
                <?php echo e($favouriteFlash['message']); ?>
            </div>
        <?php endif; ?>

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
                             alt="<?php echo e($primaryAlt); ?>"
                             width="1200" height="800">
                    <?php else: ?>
                        <img class="vehicle-gallery__primary"
                             src="assets/images/vehicles/vehicle-placeholder.svg"
                             alt="<?php echo e('No photograph available for ' . $defaultAlt); ?>"
                             width="1200" height="800">
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
                                         width="640" height="360"
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

                    <?php if ($options): ?>
                        <section class="vehicle-options" aria-labelledby="options-heading">
                            <h2 id="options-heading">Available options</h2>
                            <p id="options-help">
                                Choose extras to preview an estimated configured price.
                                Your selections are not submitted or reserved.
                            </p>
                            <fieldset class="vehicle-options__fieldset" aria-describedby="options-help">
                                <legend class="visually-hidden">Vehicle extras</legend>
                                <?php foreach ($options as $option): ?>
                                    <?php
                                        $adjustment = (float) $option['price_adjustment'];
                                        $adjustmentLabel = ($adjustment >= 0 ? '+' : '-')
                                            . format_price(abs($adjustment));
                                    ?>
                                    <label class="vehicle-option">
                                        <input type="checkbox"
                                               class="vehicle-option__control"
                                               data-price-adjustment="<?php echo e(number_format($adjustment, 2, '.', '')); ?>"
                                               <?php echo (int) $option['is_default'] === 1 ? 'checked' : ''; ?>>
                                        <span>
                                            <strong><?php echo e($option['option_name']); ?></strong>
                                            <small>
                                                <?php echo e($option['option_group']); ?>
                                                (<?php echo e($adjustmentLabel); ?>)
                                            </small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="vehicle-options__total">
                                Estimated configured price:
                                <output id="configuredPrice"
                                        data-base-price="<?php echo e(number_format((float) $vehicle['price'], 2, '.', '')); ?>">
                                    <?php echo e(format_price($vehicle['price'])); ?>
                                </output>
                            </p>
                        </section>
                    <?php endif; ?>

                    <aside class="vehicle-actions" aria-label="Vehicle actions">
                        <h2>Interested in this vehicle?</h2>
                        <?php if (is_logged_in()): ?>
                            <form action="favourites.php" method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action"
                                       value="<?php echo $isFavourite ? 'remove' : 'add'; ?>">
                                <input type="hidden" name="vehicle_id"
                                       value="<?php echo (int) $vehicleId; ?>">
                                <input type="hidden" name="return_to" value="vehicle">
                                <button class="button<?php echo $isFavourite ? ' button-danger' : ''; ?>"
                                        type="submit">
                                    <?php echo $isFavourite ? 'Remove from favourites' : 'Add to favourites'; ?>
                                </button>
                            </form>
                            <p><a href="favourites.php">View all favourites</a></p>
                            <p>
                                <a class="button" href="testdrive.php?vehicle_id=<?php echo (int) $vehicleId; ?>">
                                    Request a test drive
                                </a>
                            </p>
                        <?php else: ?>
                            <p>
                                <a href="login.php">Log in</a> to add this vehicle to your favourites.
                            </p>
                            <p>
                                <a href="login.php">Log in</a> to request a test drive.
                            </p>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
