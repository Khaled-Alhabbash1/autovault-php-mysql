<?php
/**
 * AutoVault - Administrator vehicle creation.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$values = admin_vehicle_defaults();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $validated = validate_admin_vehicle_form($_POST);
        $values = $validated['values'];
        $errors = $validated['errors'];
    }

    if (!$errors) {
        try {
            $insertStmt = $pdo->prepare(
                'INSERT INTO vehicles
                    (make, model, year, price, mileage, fuel_type, transmission,
                     body_type, color, doors, seats, vin, description, status, is_featured)
                 VALUES
                    (:make, :model, :year, :price, :mileage, :fuel_type, :transmission,
                     :body_type, :color, :doors, :seats, :vin, :description, :status, :is_featured)'
            );
            $insertStmt->execute([
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
            ]);

            $newVehicleId = (int) $pdo->lastInsertId();
            set_admin_flash('Vehicle created successfully.');
            header('Location: vehicle-edit.php?id=' . $newVehicleId);
            exit;
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $errors[] = 'That VIN is already assigned to another vehicle.';
            } else {
                error_log('Admin vehicle creation failed: ' . $e->getMessage());
                $errors[] = 'We could not create the vehicle right now.';
            }
        }
    }
}

$baseHref = '../';
$pageTitle = 'Add Vehicle';
$metaDescription = 'Add an AutoVault catalogue vehicle.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page admin-page--form">
        <p><a href="admin/vehicles.php">&larr; Manage vehicles</a></p>
        <h1>Add vehicle</h1>
        <p>Enter only information supported by the catalogue database.</p>

        <?php render_admin_vehicle_form(
            $values,
            $errors,
            'admin/vehicle-create.php',
            'Create vehicle'
        ); ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
