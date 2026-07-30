<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - My test-drive requests (private, dynamic)
 * -------------------------------------------------------------------------
 * The full history of the signed-in user's own test-drive requests. Kept
 * separate from the profile page so each page has one clear job.
 *
 * Security: login required; requests are read using the SESSION user id only
 * (a user can never see another user's requests), with a prepared statement.
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$userId = (int) current_user()['id'];
$requests = [];
$dbError = false;

try {
    $stmt = $pdo->prepare(
        'SELECT t.id, t.preferred_date, t.preferred_time, t.status, t.created_at,
                v.id AS vehicle_id, v.make, v.model, v.year
         FROM test_drive_requests t
         LEFT JOIN vehicles v ON v.id = t.vehicle_id
         WHERE t.user_id = :id
         ORDER BY t.created_at DESC'
    );
    $stmt->execute([':id' => $userId]);
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('My-requests query failed: ' . $e->getMessage());
    $dbError = true;
}

$pageTitle       = 'My Test-Drive Requests';
$metaDescription = 'Review the status of every test-drive request you have submitted to AutoVault.';
require __DIR__ . '/includes/header.php';
?>

    <section class="page-intro">
        <h1>My test-drive requests</h1>
        <p class="context-help"><a href="help-testdrive.php">Test-drive help</a> &middot;
           <a href="profile.php">Back to profile</a></p>
    </section>

    <?php if ($dbError): ?>
        <div class="form-errors" role="alert">
            We could not load your requests right now. Please try again later.
        </div>
    <?php elseif (empty($requests)): ?>
        <div class="no-results">
            <p>You have not requested any test drives yet.</p>
            <p><a href="catalogue.php">Browse the catalogue</a> to find a vehicle.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <caption class="visually-hidden">Your test-drive requests and their status</caption>
                <thead>
                    <tr>
                        <th scope="col">Vehicle</th>
                        <th scope="col">Preferred date</th>
                        <th scope="col">Preferred time</th>
                        <th scope="col">Status</th>
                        <th scope="col">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td>
                                <?php if ($request['vehicle_id'] !== null): ?>
                                    <a href="vehicle.php?id=<?php echo (int) $request['vehicle_id']; ?>">
                                        <?php echo e($request['year'] . ' ' . $request['make'] . ' ' . $request['model']); ?>
                                    </a>
                                <?php else: ?>
                                    Vehicle no longer listed
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($request['preferred_date'] ?? '-'); ?></td>
                            <td><?php echo e($request['preferred_time'] ?? '-'); ?></td>
                            <td>
                                <span class="status-badge status-badge--<?php echo e($request['status']); ?>">
                                    <?php echo e(ucfirst($request['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo e($request['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php
require __DIR__ . '/includes/footer.php';
