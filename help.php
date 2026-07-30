<?php
/**
 * AutoVault - Public help page.
 */

$pageTitle = 'Help';
$metaDescription = 'Learn how to browse vehicles and use your AutoVault account.';
$metaKeywords = 'AutoVault help, vehicle marketplace guide, account help';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>

    <article class="help-page">
        <header class="page-intro">
            <h1>AutoVault help</h1>
            <p>Use this guide to browse vehicles, manage favourites, and request a test drive.</p>
        </header>

        <?php render_help_navigation('overview'); ?>

        <section id="browse">
            <h2>Browse and search for vehicles</h2>
            <p>
                Open the <a href="catalogue.php">vehicle catalogue</a> to see currently
                available vehicles. Use the search box and filters to narrow the list by
                make, model, fuel type, transmission, year, or price. You can also change
                the sort order. Choose <strong>Apply filters</strong> after making changes.
            </p>
            <p>
                Select a vehicle name or image to open its details page. The details page
                shows its price, specifications, description, images, and available options.
            </p>
        </section>

        <section id="account">
            <h2>Create an account and log in</h2>
            <p>
                Choose <a href="register.php">Register</a>, enter your name and email address,
                and create a password of at least eight characters. After registration, use
                the <a href="login.php">login page</a> with the same email and password.
            </p>
            <p>
                If login fails, check the email spelling, keyboard state, and password.
                Disabled accounts cannot log in. AutoVault uses one general message for
                incorrect login details to protect account privacy.
            </p>
        </section>

        <section id="favourites">
            <h2>Add or remove favourites</h2>
            <p>
                Log in and open an available vehicle. Choose <strong>Add to favourites</strong>
                to save it. Open <strong>Favourites</strong> from the navigation to review saved
                vehicles or remove one. Favourites belong only to the signed-in account.
            </p>
        </section>

        <section id="test-drive">
            <h2>Request a test drive</h2>
            <p>
                Log in, open an available vehicle, and choose <strong>Request a test drive</strong>.
                Select a preferred future date. A time, phone number, and message are optional.
                Your account name and email are attached securely by the server.
            </p>
            <p>
                AutoVault prevents duplicate active requests for the same vehicle and date.
                Submitting a request does not confirm an appointment; an administrator must
                review its status.
            </p>
        </section>

        <section id="theme">
            <h2>Change the colour theme</h2>
            <p>
                Use the theme button in the main navigation to switch between light and dark
                themes. The browser remembers your choice. Before you choose, AutoVault follows
                your operating system preference when the browser supports it.
            </p>
        </section>

        <section id="media">
            <h2>Watch vehicle media</h2>
            <p>
                The <a href="media.php">Media</a> page provides three student-supplied
                automotive videos with user-controlled playback. See
                <a href="help-media.php">Media help</a> for compatibility and maintenance guidance.
            </p>
        </section>

        <section id="problems">
            <h2>Common form and login problems</h2>
            <ul>
                <li>If a form reports that the session expired, reload the page and submit it again.</li>
                <li>Complete every field marked as required and follow the message shown near the form.</li>
                <li>Use a future date for a test-drive request.</li>
                <li>If a vehicle cannot be opened, it may have been sold, deactivated, or removed.</li>
                <li>If the database is temporarily unavailable, wait and try again later.</li>
            </ul>
        </section>

        <section id="privacy">
            <h2>Privacy and security basics</h2>
            <p>
                Keep your password private and log out when using a shared computer. AutoVault
                stores passwords as secure hashes, protects data-changing forms against forged
                requests, and does not display account passwords. No public contact method is
                configured in this project, so contact details are not listed here.
            </p>
        </section>
    </article>

<?php require __DIR__ . '/includes/footer.php'; ?>
