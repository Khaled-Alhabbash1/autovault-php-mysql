<?php
$pageTitle = 'Account Help';
$metaDescription = 'Help with AutoVault registration, login, account access, and logout.';
$metaKeywords = 'account help, registration, login, logout';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>
    <article class="help-page">
        <h1>Account help</h1>
        <?php render_help_navigation('account'); ?>

        <section>
            <h2>Register</h2>
            <p>
                Use <a href="register.php">Register</a> and provide your name, email,
                and a password of at least eight characters. Registration creates a normal
                user account; administrator access cannot be requested through the form.
            </p>
        </section>
        <section>
            <h2>Log in and log out</h2>
            <p>
                Enter the registered email and password on the <a href="login.php">Login</a>
                page. Incorrect details use a general privacy-preserving message. Use the
                Logout button when finished, especially on a shared computer.
            </p>
        </section>
        <section>
            <h2>Common problems</h2>
            <ul>
                <li>Check spelling and keyboard state if login fails.</li>
                <li>Reload a form if its session-expired message appears.</li>
                <li>A disabled account cannot log in until an administrator reactivates it.</li>
                <li>Password reset and email verification are not currently available.</li>
            </ul>
        </section>
    </article>
<?php require __DIR__ . '/includes/footer.php'; ?>
