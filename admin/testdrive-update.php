<?php
/**
 * AutoVault - POST-only test-drive status update.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    $baseHref = '../';
    $pageTitle = 'Method not allowed';
    require __DIR__ . '/../includes/header.php';
    ?>
        <section class="vehicle-message" role="alert">
            <h1>Method not allowed</h1>
            <p>Request status can only be changed from an administrator form.</p>
            <a class="button" href="admin/testdrives.php">View requests</a>
        </section>
    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$requestId = parse_admin_id($_POST['request_id'] ?? null);
$status = isset($_POST['status']) && is_string($_POST['status'])
    ? $_POST['status']
    : '';
$returnTo = isset($_POST['return_to']) && is_string($_POST['return_to'])
    ? $_POST['return_to']
    : '';
$returnUrl = $returnTo === 'view' && $requestId !== null
    ? 'testdrive-view.php?id=' . $requestId
    : 'testdrives.php';

if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    set_admin_flash('Your session expired. Please try again.', 'error');
} elseif ($requestId === null || !in_array($status, admin_request_statuses(), true)) {
    set_admin_flash('The request-status update was invalid.', 'error');
} else {
    try {
        $requestStmt = $pdo->prepare(
            'SELECT 1 FROM test_drive_requests WHERE id = :id LIMIT 1'
        );
        $requestStmt->execute([':id' => $requestId]);

        if (!$requestStmt->fetchColumn()) {
            set_admin_flash('The requested test-drive record was not found.', 'error');
        } else {
            $updateStmt = $pdo->prepare(
                'UPDATE test_drive_requests SET status = :status WHERE id = :id'
            );
            $updateStmt->execute([
                ':status' => $status,
                ':id' => $requestId,
            ]);
            set_admin_flash('Test-drive request status updated.');
        }
    } catch (PDOException $e) {
        error_log('Admin test-drive status update failed: ' . $e->getMessage());
        set_admin_flash('We could not update the request right now.', 'error');
    }
}

header('Location: ' . $returnUrl);
exit;
