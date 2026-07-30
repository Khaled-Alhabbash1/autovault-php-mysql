<?php
/**
 * AutoVault - Current user's saved vehicles and favourite POST actions.
 *
 * All writes happen through POST, use the authenticated session user ID, and
 * finish with a redirect so refreshing cannot repeat an action.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/vehicle-functions.php';
require_once __DIR__ . '/includes/favourite-functions.php';

// Favourites are private account data.
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$userId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = parse_vehicle_id($_POST['vehicle_id'] ?? null);
    $returnUrl = favourite_return_url($_POST['return_to'] ?? '', $vehicleId);

    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_favourite_flash('Your session expired. Please try again.', 'error');
        header('Location: ' . $returnUrl);
        exit;
    }

    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if ($vehicleId === null || !in_array($action, ['add', 'remove'], true)) {
        set_favourite_flash('The favourite request was invalid.', 'error');
        header('Location: ' . $returnUrl);
        exit;
    }

    try {
        if ($action === 'add') {
            // Only publicly available vehicles may be added.
            $vehicleStmt = $pdo->prepare(
                "SELECT 1
                 FROM vehicles
                 WHERE id = :vehicle_id AND status = :status
                 LIMIT 1"
            );
            $vehicleStmt->execute([
                ':vehicle_id' => $vehicleId,
                ':status' => 'available',
            ]);

            if (!$vehicleStmt->fetchColumn()) {
                set_favourite_flash('That vehicle is unavailable or does not exist.', 'error');
            } else {
                try {
                    $addStmt = $pdo->prepare(
                        "INSERT INTO favourites (user_id, vehicle_id)
                         VALUES (:user_id, :vehicle_id)"
                    );
                    $addStmt->execute([
                        ':user_id' => $userId,
                        ':vehicle_id' => $vehicleId,
                    ]);
                    set_favourite_flash('Vehicle added to your favourites.');
                } catch (PDOException $e) {
                    // MySQL error 1062 is the unique user/vehicle constraint.
                    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                        set_favourite_flash('This vehicle is already in your favourites.');
                    } else {
                        throw $e;
                    }
                }
            }
        } else {
            // Both columns are required, so one user cannot delete another
            // user's favourite even if they know the vehicle ID.
            $removeStmt = $pdo->prepare(
                "DELETE FROM favourites
                 WHERE user_id = :user_id AND vehicle_id = :vehicle_id"
            );
            $removeStmt->execute([
                ':user_id' => $userId,
                ':vehicle_id' => $vehicleId,
            ]);

            if ($removeStmt->rowCount() > 0) {
                set_favourite_flash('Vehicle removed from your favourites.');
            } else {
                set_favourite_flash('That vehicle was not in your favourites.');
            }
        }
    } catch (PDOException $e) {
        error_log('Favourite action failed: ' . $e->getMessage());
        set_favourite_flash('We could not update your favourites right now.', 'error');
    }

    header('Location: ' . $returnUrl);
    exit;
}

$flash = take_favourite_flash();
$vehicles = [];
$dbError = false;

try {
    // The session user ID is the only ownership input used by this query.
    $favouritesStmt = $pdo->prepare(
        "SELECT v.id, v.make, v.model, v.year, v.price, v.mileage,
                v.body_type, v.transmission, v.fuel_type,
                pi.image_path, pi.alt_text
         FROM favourites f
         INNER JOIN vehicles v ON v.id = f.vehicle_id
         LEFT JOIN vehicle_images pi ON pi.id = (
             SELECT id
             FROM vehicle_images
             WHERE vehicle_id = v.id
             ORDER BY is_primary DESC, sort_order ASC, id ASC
             LIMIT 1
         )
         WHERE f.user_id = :user_id AND v.status = :status
         ORDER BY f.created_at DESC, f.id DESC"
    );
    $favouritesStmt->execute([
        ':user_id' => $userId,
        ':status' => 'available',
    ]);
    $vehicles = $favouritesStmt->fetchAll();
} catch (PDOException $e) {
    error_log('Favourites list query failed: ' . $e->getMessage());
    $dbError = true;
}

$pageTitle = 'My Favourites';
$metaDescription = 'View your saved vehicles at AutoVault.';
require __DIR__ . '/includes/header.php';
?>

    <section class="favourites-page">
        <div class="favourites-page__header">
            <div>
                <h1>My Favourites</h1>
                <p>Your saved, currently available vehicles.</p>
                <p class="context-help"><a href="help-favourites.php">Favourites help</a></p>
            </div>
            <a class="button button-secondary" href="catalogue.php">Browse catalogue</a>
        </div>

        <?php if ($flash !== null): ?>
            <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                 role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($dbError): ?>
            <div class="form-errors" role="alert">
                We could not load your favourites right now. Please try again later.
            </div>
        <?php elseif (!$vehicles): ?>
            <div class="no-results">
                <h2>No favourites yet</h2>
                <p>Save a vehicle from its details page and it will appear here.</p>
                <a class="button" href="catalogue.php">Find a vehicle</a>
            </div>
        <?php else: ?>
            <ul class="vehicle-grid">
                <?php foreach ($vehicles as $vehicle): ?>
                    <?php
                        $vehicleId = (int) $vehicle['id'];
                        $imageSrc = catalogue_image_src($vehicle['image_path'] ?? null);
                        $altText = trim((string) ($vehicle['alt_text'] ?? ''));
                        if ($altText === '') {
                            $altText = $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'];
                        }
                    ?>
                    <li class="vehicle-card favourite-card">
                        <a class="favourite-card__link"
                           href="vehicle.php?id=<?php echo $vehicleId; ?>">
                            <?php if ($imageSrc !== null): ?>
                                <img class="vehicle-card__img"
                                     src="<?php echo e($imageSrc); ?>"
                                     alt="<?php echo e($altText); ?>"
                                     width="640" height="360"
                                     loading="lazy">
                            <?php else: ?>
                                <img class="vehicle-card__img"
                                     src="assets/images/vehicles/vehicle-placeholder.svg"
                                     alt="<?php echo e('No photograph available for ' . $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>"
                                     width="640" height="360"
                                     loading="lazy">
                            <?php endif; ?>
                        </a>

                        <div class="vehicle-card__body">
                            <h2 class="vehicle-card__title">
                                <a href="vehicle.php?id=<?php echo $vehicleId; ?>">
                                    <?php echo e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
                                </a>
                            </h2>
                            <p class="vehicle-card__price"><?php echo e(format_price($vehicle['price'])); ?></p>
                            <ul class="vehicle-card__meta">
                                <li><?php echo e(format_mileage($vehicle['mileage'])); ?></li>
                                <?php if (!empty($vehicle['body_type'])): ?>
                                    <li><?php echo e($vehicle['body_type']); ?></li>
                                <?php endif; ?>
                                <li><?php echo e($vehicle['transmission']); ?></li>
                                <li><?php echo e($vehicle['fuel_type']); ?></li>
                            </ul>

                            <form class="favourite-card__remove" action="favourites.php" method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicleId; ?>">
                                <input type="hidden" name="return_to" value="favourites">
                                <button class="button button-danger" type="submit">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
