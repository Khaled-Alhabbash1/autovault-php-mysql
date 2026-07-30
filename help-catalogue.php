<?php
$pageTitle = 'Catalogue Help';
$metaDescription = 'Help with AutoVault catalogue search, filters, vehicle details, and images.';
$metaKeywords = 'catalogue help, vehicle search, vehicle filters';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>
    <article class="help-page">
        <h1>Catalogue and vehicle help</h1>
        <?php render_help_navigation('catalogue'); ?>

        <section>
            <h2>Browse and search</h2>
            <p>
                Open the <a href="catalogue.php">Catalogue</a> to see available vehicles.
                Search by make or model, or narrow the results with the labelled make,
                fuel, transmission, year, and price controls.
            </p>
        </section>
        <section>
            <h2>Sort and move between pages</h2>
            <p>
                Choose one of the supplied sort orders and apply the filters. Pagination
                links retain valid filters. Clear filters to return to the complete list.
            </p>
        </section>
        <section>
            <h2>Vehicle details and images</h2>
            <p>
                Select a vehicle card to review price, mileage, specifications, available
                options, description, and photographs. A local placeholder is shown when
                no safe photograph is assigned. Invalid or unavailable vehicle links return
                a clear message instead of revealing database details.
            </p>
        </section>
    </article>
<?php require __DIR__ . '/includes/footer.php'; ?>
