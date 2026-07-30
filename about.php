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
            <li>Watch the supplied vehicle media gallery.</li>
        </ul>

        <h2>Business case</h2>
        <p>
            AutoVault is an online catalogue and dealership-style service website
            for a small vehicle retailer. It presents a mixed inventory of
            everyday and enthusiast vehicles - compact and classic sedans,
            hatchbacks, convertibles, muscle cars and a range of SUVs - each with
            attributes such as year, price, mileage, body type, transmission and
            fuel type so customers can compare them properly. Casual
            <strong>visitors</strong> can browse the catalogue, search and filter
            it, view full vehicle details and watch the media gallery without an
            account. <strong>Registered customers</strong> can additionally save
            vehicles to a private favourites list, submit structured test-drive
            requests and review their own request history from their profile.
            <strong>Administrators</strong> manage the catalogue (create, edit and
            deactivate vehicles), administer user accounts (search and
            enable/disable them), process test-drive requests, and monitor basic
            application health. A MySQL database underpins all of this: vehicles
            and their images and options live in related tables, users are linked
            to their favourites and test-drive requests by foreign keys, and
            status fields drive what appears publicly - so the whole site is
            data-driven and easy to keep up to date from one place instead of
            scattered listings and manual enquiries.
        </p>
    </section>

    <section class="about-location" aria-labelledby="location-heading">
        <h2 id="location-heading">Where we are (demonstration location)</h2>
        <p>
            The interactive map below shows a <strong>demonstration location</strong>
            (Windsor, Ontario). It is used to illustrate the map feature only and
            is not a real dealership address.
        </p>
        <div class="map-embed">
            <iframe
                class="map-embed__frame"
                title="Demonstration dealership location on an interactive map of Windsor, Ontario"
                loading="lazy"
                src="https://www.openstreetmap.org/export/embed.html?bbox=-83.10%2C42.28%2C-82.95%2C42.36&amp;layer=mapnik&amp;marker=42.3149%2C-83.0364">
            </iframe>
        </div>
        <p class="note">
            Map data &copy; OpenStreetMap contributors.
            <a href="https://www.openstreetmap.org/?mlat=42.3149&amp;mlon=-83.0364#map=12/42.3149/-83.0364"
               target="_blank" rel="noopener noreferrer">
                Open this demonstration location in a larger map
            </a>.
        </p>
    </section>

<?php
// Load the shared footer to close the page.
require __DIR__ . '/includes/footer.php';
