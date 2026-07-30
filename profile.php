<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - User profile (private)
 * -------------------------------------------------------------------------
 * Shows the signed-in user's own account details and their test-drive
 * request history, and lets them update only their display name.
 *
 * Security notes:
 *   - Login is required; logged-out visitors are sent to login.php.
 *   - Every record is read/written using the SESSION user id only, so one
 *     user can never see or change another account (no IDOR).
 *   - The name update is POST + CSRF and touches only full_name - never the
 *     role, account status, email or password.
 *   - The password hash is never selected or displayed.
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Private page: send logged-out visitors to the login form.
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$userId = (int) current_user()['id'];
$errors = [];
$success = '';
$account = null;
$requests = [];
$dbError = false;

// --- Handle a name update (POST + CSRF only) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        if ($fullName === '') {
            $errors[] = 'Please enter your name.';
        } elseif (mb_strlen($fullName) > 100) {
            $errors[] = 'Your name must be 100 characters or fewer.';
        }

        if (!$errors) {
            try {
                // Only the current user's own name can be changed here.
                $updateStmt = $pdo->prepare(
                    'UPDATE users SET full_name = :name WHERE id = :id'
                );
                $updateStmt->execute([':name' => $fullName, ':id' => $userId]);

                // Keep the session name (used in the header greeting) in sync.
                $_SESSION['user']['name'] = $fullName;

                // Post/Redirect/Get so a refresh does not resubmit.
                $_SESSION['flash_success'] = 'Your profile was updated.';
                header('Location: profile.php');
                exit;
            } catch (PDOException $e) {
                error_log('Profile update failed: ' . $e->getMessage());
                $errors[] = 'We could not update your profile right now.';
            }
        }
    }
}

// One-time success message set just before the redirect above.
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// --- Load the account details and the user's own request history ---
try {
    // Note: password_hash is deliberately NOT selected.
    $accountStmt = $pdo->prepare(
        'SELECT full_name, email, role, is_active, created_at
         FROM users WHERE id = :id LIMIT 1'
    );
    $accountStmt->execute([':id' => $userId]);
    $account = $accountStmt->fetch();

    $requestStmt = $pdo->prepare(
        'SELECT t.id, t.preferred_date, t.preferred_time, t.status, t.created_at,
                v.id AS vehicle_id, v.make, v.model, v.year
         FROM test_drive_requests t
         LEFT JOIN vehicles v ON v.id = t.vehicle_id
         WHERE t.user_id = :id
         ORDER BY t.created_at DESC
         LIMIT 50'
    );
    $requestStmt->execute([':id' => $userId]);
    $requests = $requestStmt->fetchAll();
} catch (PDOException $e) {
    error_log('Profile load failed: ' . $e->getMessage());
    $dbError = true;
}

$pageTitle       = 'My Profile';
$metaDescription = 'View and update your AutoVault account details and test-drive request history.';
require __DIR__ . '/includes/header.php';
?>

    <section class="profile-page">
        <h1>My profile</h1>
        <p class="context-help"><a href="help-account.php">Account &amp; login help</a></p>

        <?php if ($success !== ''): ?>
            <div class="form-success" role="status"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="form-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($dbError): ?>
            <div class="form-errors" role="alert">
                We could not load your profile right now. Please try again later.
            </div>
        <?php elseif ($account): ?>

            <section class="profile-details" aria-labelledby="account-heading">
                <h2 id="account-heading">Account details</h2>
                <dl class="profile-grid">
                    <div>
                        <dt>Name</dt>
                        <dd><?php echo e($account['full_name']); ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?php echo e($account['email']); ?></dd>
                    </div>
                    <div>
                        <dt>Account type</dt>
                        <dd><?php echo e(ucfirst($account['role'])); ?></dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>
                            <span class="status-badge status-badge--<?php echo ((int) $account['is_active'] === 1) ? 'active' : 'inactive'; ?>">
                                <?php echo ((int) $account['is_active'] === 1) ? 'Active' : 'Disabled'; ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Member since</dt>
                        <dd><?php echo e($account['created_at']); ?></dd>
                    </div>
                </dl>
                <p><a href="favourites.php">View your saved favourites</a></p>
            </section>

            <section class="profile-edit" aria-labelledby="edit-heading">
                <h2 id="edit-heading">Update your name</h2>
                <form class="auth-form" action="profile.php" method="post" novalidate>
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="full_name">Full name</label>
                        <input type="text" id="full_name" name="full_name"
                               value="<?php echo e($account['full_name']); ?>"
                               maxlength="100" required>
                    </div>
                    <button type="submit" class="button">Save changes</button>
                </form>
            </section>

            <section class="profile-requests" aria-labelledby="requests-heading">
                <h2 id="requests-heading">My test-drive requests</h2>
                <?php if (empty($requests)): ?>
                    <p class="note">
                        You have not requested any test drives yet.
                        <a href="catalogue.php">Browse the catalogue</a> to get started.
                    </p>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <caption class="visually-hidden">Your test-drive requests</caption>
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
            </section>

        <?php endif; ?>
    </section>

<?php
require __DIR__ . '/includes/footer.php';
