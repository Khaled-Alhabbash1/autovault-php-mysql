<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - User registration
 * -------------------------------------------------------------------------
 * Shows a registration form and creates a new NORMAL user account.
 *
 * Security notes for beginners:
 *   - All checks happen on the SERVER (never trust the browser).
 *   - The form is protected by a CSRF token.
 *   - Passwords are hashed with password_hash() and never stored as text.
 *   - The database is only touched with PDO prepared statements.
 *   - The user cannot choose the "admin" role: we always insert 'user'.
 * -------------------------------------------------------------------------
 */

// Load the session/CSRF helper and the database connection.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// If the visitor is already logged in, there is no need to register.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// These hold any error messages and the previously typed values so the
// form can be redisplayed without the user retyping everything.
$errors = [];
$old = [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
];

// Only process data when the form was actually submitted with POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Check the CSRF token first.
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try submitting the form again.';
    } else {

        // 2. Read and trim the submitted values (passwords are not trimmed
        //    so that spaces the user intended are kept).
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['password_confirm'] ?? '';

        // Remember the text values for redisplay (never the password).
        $old['first_name'] = $firstName;
        $old['last_name']  = $lastName;
        $old['email']      = $email;

        // 3. Validate everything on the server.
        if ($firstName === '') {
            $errors[] = 'Please enter your first name.';
        } elseif (mb_strlen($firstName) > 50) {
            $errors[] = 'First name is too long.';
        }

        if ($lastName === '') {
            $errors[] = 'Please enter your last name.';
        } elseif (mb_strlen($lastName) > 50) {
            $errors[] = 'Last name is too long.';
        }

        if ($email === '') {
            $errors[] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (mb_strlen($password) < 8) {
            $errors[] = 'Your password must be at least 8 characters long.';
        } elseif (mb_strlen($password) > 255) {
            $errors[] = 'Your password is too long.';
        }

        if ($password !== $confirm) {
            $errors[] = 'The two passwords do not match.';
        }

        // 4. If the basic checks passed, make sure the email is not taken.
        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with that email address already exists.';
            }
        }

        // 5. All good: create the account.
        if (empty($errors)) {

            // Combine the two names into the single full_name column.
            $fullName = $firstName . ' ' . $lastName;

            // Hash the password. We NEVER store the plain text.
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Note: role is hard-coded to 'user'. The visitor can never
                // make themselves an administrator.
                $insert = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash, role)
                     VALUES (?, ?, ?, ?)'
                );
                $insert->execute([$fullName, $email, $passwordHash, 'user']);

                // Store a one-time success message and send them to login
                // (Post/Redirect/Get so a page refresh does not resubmit).
                $_SESSION['flash_success'] =
                    'Your account was created. You can now log in.';
                header('Location: login.php');
                exit;

            } catch (PDOException $ex) {
                // A duplicate email can slip past the check above if two
                // people register at the same instant; the UNIQUE key stops
                // it. Show a friendly message, never the raw database error.
                if ($ex->getCode() === '23000') {
                    $errors[] = 'An account with that email address already exists.';
                } else {
                    $errors[] = 'Sorry, we could not create your account. Please try again.';
                }
            }
        }
    }
}

// ---- Display the page ----
$pageTitle       = 'Register';
$metaDescription = 'Create a new AutoVault account.';
require __DIR__ . '/includes/header.php';
?>

    <section class="auth-page">
        <h1>Create your account</h1>

        <?php if (!empty($errors)): ?>
            <!-- Show any validation errors. Each message is escaped. -->
            <div class="form-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="auth-form" action="register.php" method="post" novalidate>

            <!-- Hidden CSRF token protects this form -->
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="first_name">First name</label>
                <input type="text" id="first_name" name="first_name"
                       value="<?php echo e($old['first_name']); ?>"
                       maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last name</label>
                <input type="text" id="last_name" name="last_name"
                       value="<?php echo e($old['last_name']); ?>"
                       maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo e($old['email']); ?>"
                       maxlength="150" required>
            </div>

            <div class="form-group">
                <label for="password">Password (at least 8 characters)</label>
                <input type="password" id="password" name="password"
                       minlength="8" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       minlength="8" required>
            </div>

            <button type="submit" class="button">Create account</button>
        </form>

        <p class="auth-alt">
            Already have an account? <a href="login.php">Log in here</a>.
        </p>
    </section>

<?php
require __DIR__ . '/includes/footer.php';
