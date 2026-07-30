<?php
/**
 * AutoVault - Confirm and soft-deactivate a vehicle.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$vehicleId = parse_vehicle_id($isPost
    ? ($_POST['vehicle_id'] ?? null)
    : ($_GET['id'] ?? null));
$vehicle = null;
$pageError = null;
$errors = [];

if ($vehicleId === null) {
    http_response_code(400);
    $pageError = 'invalid';
} else {
    try {
        $vehicleStmt = $pdo->prepare(
            'SELECT make, model, year, status
             FROM vehicles
             WHERE id = :id
             LIMIT 1'
        );
        $vehicleStmt->execute([':id' => $vehicleId]);
        $vehicle = $vehicleStmt->fetch();

        if (!$vehicle) {
            http_response_code(404);
            $pageError = 'not-found';
        }
    } catch (PDOException $e) {
        error_log('Admin vehicle deactivation lookup failed: ' . $e->getMessage());
        http_response_code(500);
        $pageError = 'database';
    }
}

if ($pageError === null && $isPost) {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        try {
            // Soft deactivation preserves images, favourites and request links.
            $deactivateStmt = $pdo->prepare(
                "UPDATE vehicles
                 SET status = :status
                 WHERE id = :id"
            );
            $deactivateStmt->execute([
                ':status' => 'sold',
                ':id' => $vehicleId,
            ]);

            set_admin_flash('Vehicle deactivated. Related records were preserved.');
            header('Location: vehicles.php');
            exit;
        } catch (PDOException $e) {
            error_log('Admin vehicle deactivation failed: ' . $e->getMessage());
            $errors[] = 'We could not deactivate the vehicle right now.';
        }
    }
}

$baseHref = '../';
$pageTitle = 'Deactivate Vehicle';
$metaDescription = 'Deactivate an AutoVault catalogue vehicle.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page admin-page--form">
        <p><a href="admin/vehicles.php">&larr; Manage vehicles</a></p>

        <?php if ($pageError !== null): ?>
            <div class="vehicle-message" role="alert">
                <?php if ($pageError === 'invalid'): ?>
                    <h1>Invalid vehicle ID</h1>
                    <p>The vehicle ID must be a positive whole number.</p>
                <?php elseif ($pageError === 'not-found'): ?>
                    <h1>Vehicle not found</h1>
                    <p>The requested vehicle could not be found.</p>
                <?php else: ?>
                    <h1>Vehicle unavailable</h1>
                    <p>We could not load this vehicle right now.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-confirm">
                <h1>Deactivate vehicle?</h1>
                <p>
                    <strong><?php echo e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></strong>
                    will be marked sold and removed from public listings.
                </p>
                <p>Images, favourites, and test-drive request records will remain linked.</p>

                <?php if ($errors): ?>
                    <div class="form-errors" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="admin/vehicle-delete.php" method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="vehicle_id" value="<?php echo (int) $vehicleId; ?>">
                    <button class="button button-danger" type="submit">Deactivate vehicle</button>
                    <a class="button button-secondary" href="admin/vehicles.php">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
