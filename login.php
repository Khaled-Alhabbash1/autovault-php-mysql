<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - User login
 * -------------------------------------------------------------------------
 * Shows a login form and signs the user in.
 *
 * Security notes for beginners:
 *   - The form is protected by a CSRF token.
 *   - The user is looked up with a PDO prepared statement.
 *   - The password is checked with password_verify().
 *   - A SINGLE generic error is shown so the page never reveals whether an
 *     email address exists (this stops "account enumeration").
 *   - After a successful login the session ID is regenerated.
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// If already logged in, just go to the home page.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = ['email' => ''];

// Pick up a one-time success message (e.g. set after registering).
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verify the CSRF token.
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try logging in again.';
    } else {

        // 2. Read the submitted values.
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $old['email'] = $email;

        // 3. Basic presence checks.
        if ($email === '' || $password === '') {
            $errors[] = 'Please enter both your email and password.';
        } else {

            // 4. Look the user up with a prepared statement.
            $stmt = $pdo->prepare(
                'SELECT id, full_name, password_hash, role, is_active
                 FROM users WHERE email = ? LIMIT 1'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // 5. Verify the password. The check below is written so that a
            //    wrong email and a wrong password give the SAME message.
            if ($user && password_verify($password, $user['password_hash'])) {

                if ((int) $user['is_active'] !== 1) {
                    // Correct password but the account is disabled by an admin.
                    $errors[] = 'This account has been disabled. Please contact support.';
                } else {
                    // Success! Regenerate the session ID to prevent
                    // "session fixation" attacks.
                    session_regenerate_id(true);

                    // Store only the minimum we need in the session.
                    $_SESSION['user'] = [
                        'id'   => (int) $user['id'],
                        'name' => $user['full_name'],
                        'role' => $user['role'],
                    ];

                    // Post/Redirect/Get: send them to the home page.
                    header('Location: index.php');
                    exit;
                }
            } else {
                // Generic message: do not reveal which part was wrong.
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}

// ---- Display the page ----
$pageTitle       = 'Login';
$metaDescription = 'Log in to your AutoVault account.';
$metaKeywords = 'AutoVault login, vehicle account';
require __DIR__ . '/includes/header.php';
?>

    <section class="auth-page">
        <h1>Log in</h1>
        <p class="context-help"><a href="help-account.php">Account and login help</a></p>

        <?php if ($flashSuccess !== ''): ?>
            <div class="form-success" role="status">
                <?php echo e($flashSuccess); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="form-errors" id="login-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="auth-form" action="login.php" method="post" novalidate
              <?php echo $errors ? 'aria-describedby="login-errors"' : ''; ?>>

            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo e($old['email']); ?>"
                       maxlength="150" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="button">Log in</button>
        </form>

        <p class="auth-alt">
            Don't have an account? <a href="register.php">Register here</a>.
        </p>
    </section>

<?php
require __DIR__ . '/includes/footer.php';
