<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Privacy statement (static informational page)
 * -------------------------------------------------------------------------
 * Plain informational content that reuses the shared layout. It describes,
 * honestly, what data the application stores and how it is used. It does not
 * claim any real company, address, or contact details.
 * -------------------------------------------------------------------------
 */

$pageTitle       = 'Privacy';
$metaDescription = 'How AutoVault, a university demonstration project, stores and uses account and request data.';
require __DIR__ . '/includes/header.php';
?>

    <section class="page-intro">
        <h1>Privacy</h1>
        <p>
            AutoVault is a university demonstration project. This page explains,
            honestly, what information the application stores and how it is used.
        </p>
    </section>

    <section class="about-details">
        <h2>What we store</h2>
        <ul>
            <li>Your name and email address, provided when you register.</li>
            <li>Your password, stored only as a secure one-way hash - never in plain text.</li>
            <li>The vehicles you save to your favourites.</li>
            <li>The test-drive requests you submit, including your chosen date, time, phone number and message.</li>
        </ul>

        <h2>How it is used</h2>
        <ul>
            <li>To sign you in and show your private favourites and request history.</li>
            <li>To let administrators review and update the status of test-drive requests.</li>
            <li>To display safe, aggregate counts (for example, vehicles by body type) on the administrator monitoring page - never your personal details.</li>
        </ul>

        <h2>What we do not do</h2>
        <ul>
            <li>We do not sell your data or share it with third parties.</li>
            <li>We do not process real payments.</li>
            <li>We do not use tracking advertising cookies. A single browser setting stores only your chosen colour theme.</li>
        </ul>

        <h2>Your choices</h2>
        <p>
            You can update your display name from your <a href="profile.php">profile</a>.
            Because this is a demonstration project, requests to remove an account
            should be directed to the project owner or supervising instructor
            rather than to any published contact address.
        </p>

        <p class="note">
            This statement describes the demonstration project only and is not a
            legal privacy policy for a real business.
        </p>
    </section>

<?php
require __DIR__ . '/includes/footer.php';
