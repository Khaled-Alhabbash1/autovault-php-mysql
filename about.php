<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - About page
 * -------------------------------------------------------------------------
 * A simple static information page that uses the reusable header and footer.
 * No database connection is needed here.
 * -------------------------------------------------------------------------
 */

// Set the page title and description, then load the shared header.
$pageTitle       = 'About';
$metaDescription = 'Learn more about AutoVault, a student-built vehicle marketplace.';
require __DIR__ . '/includes/header.php';
?>

    <section class="page-intro">
        <h1>About AutoVault</h1>
        <p>
            AutoVault is a vehicle marketplace built as a university project. It
            lets people browse vehicles, create an account, save favourites and
            request test drives, while administrators manage the catalogue and
            the website.
        </p>
    </section>

    <section class="about-details">
        <h2>How the project is built</h2>
        <p>
            The site is built with plain PHP and MySQL - no frameworks. It uses
            reusable header and footer includes, external CSS and external
            JavaScript, and follows accessible, semantic HTML5.
        </p>

        <h2>What you can do</h2>
        <ul>
            <li>Browse a catalogue of vehicles.</li>
            <li>Create an account and log in.</li>
            <li>Save vehicles to a favourites list.</li>
            <li>Request a test drive for a vehicle.</li>
        </ul>

        <p class="note">
            This project is being built in small milestones, so some features
            become available as development continues.
        </p>
    </section>

<?php
// Load the shared footer to close the page.
require __DIR__ . '/includes/footer.php';
