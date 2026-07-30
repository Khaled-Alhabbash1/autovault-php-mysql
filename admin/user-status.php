<?php
/**
 * AutoVault - POST-only account activation/deactivation.
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
            <p>Account status can only be changed from the user management form.</p>
            <a class="button" href="admin/users.php">Manage users</a>
        </section>
    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$userId = parse_admin_id($_POST['user_id'] ?? null);
$action = isset($_POST['action']) && is_string($_POST['action'])
    ? $_POST['action']
    : '';

if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    set_admin_flash('Your session expired. Please try again.', 'error');
} elseif ($userId === null || !in_array($action, ['activate', 'deactivate'], true)) {
    set_admin_flash('The account-status request was invalid.', 'error');
} elseif ($userId === (int) current_user()['id'] && $action === 'deactivate') {
    set_admin_flash('You cannot deactivate your own current account.', 'error');
} else {
    try {
        $userStmt = $pdo->prepare(
            'SELECT role, is_active FROM users WHERE id = :id LIMIT 1'
        );
        $userStmt->execute([':id' => $userId]);
        $target = $userStmt->fetch();

        if (!$target) {
            set_admin_flash('The requested account was not found.', 'error');
        } elseif (
            $action === 'deactivate'
            && $target['role'] === 'admin'
            && (int) $target['is_active'] === 1
        ) {
            $adminCountStmt = $pdo->prepare(
                "SELECT COUNT(*) FROM users
                 WHERE role = :role AND is_active = 1"
            );
            $adminCountStmt->execute([':role' => 'admin']);

            if ((int) $adminCountStmt->fetchColumn() <= 1) {
                set_admin_flash('The final active administrator cannot be deactivated.', 'error');
            } else {
                $statusStmt = $pdo->prepare(
                    'UPDATE users SET is_active = 0 WHERE id = :id'
                );
                $statusStmt->execute([':id' => $userId]);
                set_admin_flash('Account deactivated.');
            }
        } else {
            $statusStmt = $pdo->prepare(
                'UPDATE users SET is_active = :is_active WHERE id = :id'
            );
            $statusStmt->execute([
                ':is_active' => $action === 'activate' ? 1 : 0,
                ':id' => $userId,
            ]);
            set_admin_flash($action === 'activate'
                ? 'Account activated.'
                : 'Account deactivated.');
        }
    } catch (PDOException $e) {
        error_log('Admin account status update failed: ' . $e->getMessage());
        set_admin_flash('We could not update the account right now.', 'error');
    }
}

header('Location: users.php');
exit;
