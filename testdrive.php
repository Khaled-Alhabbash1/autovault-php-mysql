<?php
/**
 * AutoVault - Authenticated test-drive request form.
 */

require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vehicle-functions.php';
require_once __DIR__ . '/includes/testdrive-functions.php';

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$vehicleId = parse_vehicle_id($isPost
    ? ($_POST['vehicle_id'] ?? null)
    : ($_GET['vehicle_id'] ?? null));

$vehicle = null;
$account = null;
$pageError = null;
$flash = take_testdrive_flash();
$errors = [];
$values = [
    'preferred_date' => '',
    'preferred_time' => '',
    'phone' => '',
    'message' => '',
];

if ($vehicleId === null) {
    http_response_code(400);
    $pageError = 'invalid';
} else {
    try {
        // The public-status condition does not reveal unavailable records.
        $vehicleStmt = $pdo->prepare(
            "SELECT make, model, year
             FROM vehicles
             WHERE id = :vehicle_id AND status = :status
             LIMIT 1"
        );
        $vehicleStmt->execute([
            ':vehicle_id' => $vehicleId,
            ':status' => 'available',
        ]);
        $vehicle = $vehicleStmt->fetch();

        if (!$vehicle) {
            http_response_code(404);
            $pageError = 'not-found';
        } else {
            // Full name and email are copied from the authenticated account,
            // not accepted from hidden fields or other client input.
            $accountStmt = $pdo->prepare(
                "SELECT full_name, email
                 FROM users
                 WHERE id = :user_id AND is_active = 1
                 LIMIT 1"
            );
            $accountStmt->execute([':user_id' => (int) current_user()['id']]);
            $account = $accountStmt->fetch();

            if (!$account) {
                http_response_code(403);
                $pageError = 'account';
            }
        }
    } catch (PDOException $e) {
        error_log('Test-drive setup query failed: ' . $e->getMessage());
        http_response_code(500);
        $pageError = 'database';
    }
}

if ($pageError === null && $isPost) {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $validated = validate_testdrive_form($_POST);
        $values = $validated['values'];
        $errors = $validated['errors'];
    }

    if (!$errors) {
        try {
            // Accidental-repeat rule: one active request for the same user,
            // vehicle and preferred date. Future dates remain independent.
            $duplicateStmt = $pdo->prepare(
                "SELECT 1
                 FROM test_drive_requests
                 WHERE user_id = :user_id
                   AND vehicle_id = :vehicle_id
                   AND preferred_date = :preferred_date
                   AND status IN ('pending', 'confirmed')
                 LIMIT 1"
            );
            $duplicateStmt->execute([
                ':user_id' => (int) current_user()['id'],
                ':vehicle_id' => $vehicleId,
                ':preferred_date' => $values['preferred_date'],
            ]);

            if ($duplicateStmt->fetchColumn()) {
                $errors[] = 'You already have an active request for this vehicle on that date.';
            } else {
                $insertStmt = $pdo->prepare(
                    "INSERT INTO test_drive_requests
                        (vehicle_id, user_id, full_name, email, phone,
                         preferred_date, preferred_time, message)
                     VALUES
                        (:vehicle_id, :user_id, :full_name, :email, :phone,
                         :preferred_date, :preferred_time, :message)"
                );
                $insertStmt->execute([
                    ':vehicle_id' => $vehicleId,
                    ':user_id' => (int) current_user()['id'],
                    ':full_name' => $account['full_name'],
                    ':email' => $account['email'],
                    ':phone' => $values['phone'] !== '' ? $values['phone'] : null,
                    ':preferred_date' => $values['preferred_date'],
                    ':preferred_time' => $values['preferred_time'] !== ''
                        ? $values['preferred_time'] . ':00'
                        : null,
                    ':message' => $values['message'] !== '' ? $values['message'] : null,
                ]);

                set_testdrive_flash('Your test-drive request was submitted.');
                header('Location: testdrive.php?vehicle_id=' . $vehicleId);
                exit;
            }
        } catch (PDOException $e) {
            error_log('Test-drive submission failed: ' . $e->getMessage());
            $errors[] = 'We could not submit your request right now. Please try again later.';
        }
    }
}

$vehicleName = $vehicle
    ? $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']
    : 'Vehicle';
$pageTitle = 'Request a Test Drive';
$metaDescription = 'Request a test drive for an available AutoVault vehicle.';
require __DIR__ . '/includes/header.php';
?>

    <section class="testdrive-page">
        <a href="<?php echo $vehicleId !== null
            ? 'vehicle.php?id=' . (int) $vehicleId
            : 'catalogue.php'; ?>">
            &larr; <?php echo $vehicleId !== null ? 'Back to vehicle' : 'Back to catalogue'; ?>
        </a>
        <span class="context-help"><a href="help-testdrive.php">Test-drive request help</a></span>

        <?php if ($pageError !== null): ?>
            <div class="vehicle-message" role="alert">
                <?php if ($pageError === 'invalid'): ?>
                    <h1>Invalid vehicle link</h1>
                    <p>The vehicle ID must be a positive whole number.</p>
                <?php elseif ($pageError === 'not-found'): ?>
                    <h1>Vehicle not found</h1>
                    <p>That vehicle is unavailable or does not exist.</p>
                <?php elseif ($pageError === 'account'): ?>
                    <h1>Account unavailable</h1>
                    <p>Your account cannot submit a request right now.</p>
                <?php else: ?>
                    <h1>Request unavailable</h1>
                    <p>We could not load the request form. Please try again later.</p>
                <?php endif; ?>
                <a class="button" href="catalogue.php">Browse vehicles</a>
            </div>
        <?php else: ?>
            <div class="testdrive-page__header">
                <p class="vehicle-details__eyebrow">Test drive</p>
                <h1><?php echo e($vehicleName); ?></h1>
                <p>Your account details will be attached securely to this request.</p>
            </div>

            <?php if ($flash !== null): ?>
                <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                     role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="form-errors" id="testdrive-errors" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="testdrive-form" action="testdrive.php" method="post" novalidate
                  <?php echo $errors ? 'aria-describedby="testdrive-errors"' : ''; ?>>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="vehicle_id" value="<?php echo (int) $vehicleId; ?>">

                <div class="form-group">
                    <label for="preferred_date">Preferred date</label>
                    <input type="date" id="preferred_date" name="preferred_date"
                           min="<?php echo e(date('Y-m-d')); ?>"
                           value="<?php echo e($values['preferred_date']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="preferred_time">Preferred time (optional)</label>
                    <input type="time" id="preferred_time" name="preferred_time"
                           value="<?php echo e($values['preferred_time']); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone number (optional)</label>
                    <input type="tel" id="phone" name="phone" maxlength="30"
                           autocomplete="tel"
                           value="<?php echo e($values['phone']); ?>">
                </div>

                <div class="form-group">
                    <label for="message">Message (optional)</label>
                    <textarea id="message" name="message" rows="6"
                              maxlength="2000"><?php echo e($values['message']); ?></textarea>
                </div>

                <button class="button" type="submit">Submit request</button>
            </form>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
