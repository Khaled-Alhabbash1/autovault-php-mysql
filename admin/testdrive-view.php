<?php
/**
 * AutoVault - Administrator test-drive request details.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$requestId = parse_admin_id($_GET['id'] ?? null);
$request = null;
$pageError = null;
$flash = take_admin_flash();

if ($requestId === null) {
    http_response_code(400);
    $pageError = 'invalid';
} else {
    try {
        $requestStmt = $pdo->prepare(
            'SELECT r.id, r.user_id, r.vehicle_id, r.full_name, r.email,
                    r.phone, r.preferred_date, r.preferred_time, r.message,
                    r.status, r.created_at,
                    u.full_name AS current_user_name, u.email AS current_user_email,
                    v.year, v.make, v.model
             FROM test_drive_requests r
             LEFT JOIN users u ON u.id = r.user_id
             LEFT JOIN vehicles v ON v.id = r.vehicle_id
             WHERE r.id = :id
             LIMIT 1'
        );
        $requestStmt->execute([':id' => $requestId]);
        $request = $requestStmt->fetch();

        if (!$request) {
            http_response_code(404);
            $pageError = 'not-found';
        }
    } catch (PDOException $e) {
        error_log('Admin test-drive details query failed: ' . $e->getMessage());
        http_response_code(500);
        $pageError = 'database';
    }
}

$baseHref = '../';
$pageTitle = 'Test-Drive Request';
$metaDescription = 'Review an AutoVault test-drive request.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page admin-page--form">
        <p><a href="admin/testdrives.php">&larr; Test-drive requests</a></p>

        <?php if ($pageError !== null): ?>
            <div class="vehicle-message" role="alert">
                <?php if ($pageError === 'invalid'): ?>
                    <h1>Invalid request ID</h1>
                    <p>The request ID must be a positive whole number.</p>
                <?php elseif ($pageError === 'not-found'): ?>
                    <h1>Request not found</h1>
                    <p>The requested test-drive record is unavailable.</p>
                <?php else: ?>
                    <h1>Request unavailable</h1>
                    <p>We could not load this request right now.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-page__header">
                <div>
                    <p class="vehicle-details__eyebrow">Request #<?php echo (int) $request['id']; ?></p>
                    <h1>Test-drive request</h1>
                </div>
                <span class="status-badge status-badge--<?php echo e($request['status']); ?>">
                    <?php echo e(ucfirst($request['status'])); ?>
                </span>
            </div>

            <?php if ($flash !== null): ?>
                <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                     role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <dl class="request-details">
                <div><dt>Name</dt><dd><?php echo e($request['full_name']); ?></dd></div>
                <div><dt>Email</dt><dd><?php echo e($request['email']); ?></dd></div>
                <div><dt>Phone</dt><dd><?php echo $request['phone'] !== null ? e($request['phone']) : 'Not provided'; ?></dd></div>
                <div>
                    <dt>Vehicle</dt>
                    <dd>
                        <?php if ($request['vehicle_id'] !== null && $request['make'] !== null): ?>
                            <a href="vehicle.php?id=<?php echo (int) $request['vehicle_id']; ?>">
                                <?php echo e($request['year'] . ' ' . $request['make'] . ' ' . $request['model']); ?>
                            </a>
                        <?php else: ?>
                            Vehicle unavailable
                        <?php endif; ?>
                    </dd>
                </div>
                <div><dt>Preferred date</dt><dd><?php echo $request['preferred_date'] !== null ? e($request['preferred_date']) : 'Not specified'; ?></dd></div>
                <div><dt>Preferred time</dt><dd><?php echo $request['preferred_time'] !== null ? e(substr($request['preferred_time'], 0, 5)) : 'Not specified'; ?></dd></div>
                <div><dt>Submitted</dt><dd><?php echo e($request['created_at']); ?></dd></div>
                <div class="request-details__wide">
                    <dt>Message</dt>
                    <dd>
                        <?php echo $request['message'] !== null && trim($request['message']) !== ''
                            ? nl2br(e($request['message']), false)
                            : 'No message provided.'; ?>
                    </dd>
                </div>
            </dl>

            <form class="admin-form request-status-form"
                  action="admin/testdrive-update.php" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>">
                <input type="hidden" name="return_to" value="view">
                <div class="form-group">
                    <label for="request-status-update">Request status</label>
                    <select id="request-status-update" name="status">
                        <?php foreach (admin_request_statuses() as $status): ?>
                            <option value="<?php echo e($status); ?>"
                                <?php echo $request['status'] === $status ? 'selected' : ''; ?>>
                                <?php echo e(ucfirst($status)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="button" type="submit">Update status</button>
            </form>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
