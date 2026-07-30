<?php
/**
 * AutoVault - Administrator vehicle editing.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$vehicleId = parse_vehicle_id($_GET['id'] ?? null);
$vehicle = null;
$values = admin_vehicle_defaults();
$errors = [];
$flash = take_admin_flash();
$pageError = null;

if ($vehicleId === null) {
    http_response_code(400);
    $pageError = 'invalid';
} else {
    try {
        $vehicleStmt = $pdo->prepare(
            'SELECT make, model, year, price, mileage, fuel_type, transmission,
                    body_type, color, doors, seats, vin, description, status, is_featured
             FROM vehicles
             WHERE id = :id
             LIMIT 1'
        );
        $vehicleStmt->execute([':id' => $vehicleId]);
        $vehicle = $vehicleStmt->fetch();

        if (!$vehicle) {
            http_response_code(404);
            $pageError = 'not-found';
        } else {
            foreach (array_keys($values) as $key) {
                $values[$key] = $vehicle[$key] === null ? '' : (string) $vehicle[$key];
            }
        }
    } catch (PDOException $e) {
        error_log('Admin vehicle edit lookup failed: ' . $e->getMessage());
        http_response_code(500);
        $pageError = 'database';
    }
}

if ($pageError === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $validated = validate_admin_vehicle_form($_POST);
        $values = $validated['values'];
        $errors = $validated['errors'];
    }

    if (!$errors) {
        try {
            // Fixed column list: no submitted field name can enter this SQL.
            $updateStmt = $pdo->prepare(
                'UPDATE vehicles SET
                    make = :make,
                    model = :model,
                    year = :year,
                    price = :price,
                    mileage = :mileage,
                    fuel_type = :fuel_type,
                    transmission = :transmission,
                    body_type = :body_type,
                    color = :color,
                    doors = :doors,
                    seats = :seats,
                    vin = :vin,
                    description = :description,
                    status = :status,
                    is_featured = :is_featured
                 WHERE id = :id'
            );
            $updateStmt->execute([
                ':make' => $values['make'],
                ':model' => $values['model'],
                ':year' => (int) $values['year'],
                ':price' => $values['price'],
                ':mileage' => $values['mileage'] !== '' ? (int) $values['mileage'] : null,
                ':fuel_type' => $values['fuel_type'],
                ':transmission' => $values['transmission'],
                ':body_type' => $values['body_type'] !== '' ? $values['body_type'] : null,
                ':color' => $values['color'] !== '' ? $values['color'] : null,
                ':doors' => $values['doors'] !== '' ? (int) $values['doors'] : null,
                ':seats' => $values['seats'] !== '' ? (int) $values['seats'] : null,
                ':vin' => $values['vin'] !== '' ? $values['vin'] : null,
                ':description' => $values['description'] !== '' ? $values['description'] : null,
                ':status' => $values['status'],
                ':is_featured' => $values['is_featured'] === '1' ? 1 : 0,
                ':id' => $vehicleId,
            ]);

            set_admin_flash('Vehicle updated successfully.');
            header('Location: vehicle-edit.php?id=' . $vehicleId);
            exit;
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $errors[] = 'That VIN is already assigned to another vehicle.';
            } else {
                error_log('Admin vehicle update failed: ' . $e->getMessage());
                $errors[] = 'We could not update the vehicle right now.';
            }
        }
    }
}

$baseHref = '../';
$pageTitle = 'Edit Vehicle';
$metaDescription = 'Edit an AutoVault catalogue vehicle.';
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
                    <p>The requested vehicle could not be edited.</p>
                <?php else: ?>
                    <h1>Vehicle unavailable</h1>
                    <p>We could not load this vehicle right now.</p>
                <?php endif; ?>
                <a class="button" href="admin/vehicles.php">Back to vehicles</a>
            </div>
        <?php else: ?>
            <h1>Edit <?php echo e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></h1>

            <?php if ($flash !== null): ?>
                <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                     role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php render_admin_vehicle_form(
                $values,
                $errors,
                'admin/vehicle-edit.php?id=' . (int) $vehicleId,
                'Save changes'
            ); ?>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
