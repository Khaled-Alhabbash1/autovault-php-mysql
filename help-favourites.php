<?php
$pageTitle = 'Favourites Help';
$metaDescription = 'Help adding, viewing, and removing AutoVault favourite vehicles.';
$metaKeywords = 'favourites help, saved vehicles';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>
    <article class="help-page">
        <h1>Favourites help</h1>
        <?php render_help_navigation('favourites'); ?>

        <section>
            <h2>Save a vehicle</h2>
            <p>
                Log in, open an available vehicle, and choose <strong>Add to favourites</strong>.
                The vehicle is saved only to the current account.
            </p>
        </section>
        <section>
            <h2>View or remove saved vehicles</h2>
            <p>
                Choose <a href="favourites.php">Favourites</a> in the navigation.
                Use the Remove button on a card to delete that saved relationship. Sold or
                unavailable vehicles are not shown in the active favourites list.
            </p>
        </section>
        <section>
            <h2>If an update fails</h2>
            <p>
                Reload the page and try again. A session-expired message means the security
                token is no longer current; no change is made until a valid form is submitted.
            </p>
        </section>
    </article>
<?php require __DIR__ . '/includes/footer.php'; ?>
