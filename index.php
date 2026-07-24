<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Home page
 * -------------------------------------------------------------------------
 * This is the landing page. It uses the reusable header and footer.
 * It does NOT connect to the database yet (no catalogue in this milestone).
 * -------------------------------------------------------------------------
 */

// Set the page title and description, then load the shared header.
$pageTitle       = 'Home';
$metaDescription = 'Browse vehicles, save your favourites and request a test drive with AutoVault.';
require __DIR__ . '/includes/header.php';
?>

    <!-- Hero / welcome banner -->
    <section class="hero">
        <h1>Find your next vehicle with AutoVault</h1>
        <p>
            Browse a wide range of vehicles, save your favourites and request a
            test drive - all in one place.
        </p>
        <p>
            <a class="button" href="catalogue.php">Browse the catalogue</a>
        </p>
    </section>

    <!-- Simple feature highlights (static content, no database needed) -->
    <section class="features">
        <h2>Why AutoVault?</h2>
        <div class="feature-grid">
            <article class="feature-card">
                <h3>Wide selection</h3>
                <p>A growing catalogue of vehicles with clear details and photos.</p>
            </article>
            <article class="feature-card">
                <h3>Save favourites</h3>
                <p>Create an account to keep a list of the vehicles you love.</p>
            </article>
            <article class="feature-card">
                <h3>Request a test drive</h3>
                <p>Book a test drive online in just a few minutes.</p>
            </article>
        </div>
    </section>

<?php
// Load the shared footer to close the page.
require __DIR__ . '/includes/footer.php';
