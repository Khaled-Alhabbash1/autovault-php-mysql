<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Accessibility statement (static informational page)
 * -------------------------------------------------------------------------
 * Describes the accessibility features that are actually implemented. It does
 * NOT claim formal WCAG certification, which would require independent testing.
 * -------------------------------------------------------------------------
 */

$pageTitle       = 'Accessibility';
$metaDescription = 'The accessibility features built into the AutoVault interface and how to report an issue.';
require __DIR__ . '/includes/header.php';
?>

    <section class="page-intro">
        <h1>Accessibility</h1>
        <p>
            AutoVault aims to be usable with a keyboard, a screen reader, and a
            range of screen sizes. This page lists what is implemented today.
        </p>
    </section>

    <section class="about-details">
        <h2>What is built in</h2>
        <ul>
            <li>Semantic <code>header</code>, <code>nav</code>, <code>main</code> and <code>footer</code> landmarks, and a "Skip to main content" link.</li>
            <li>A responsive navigation menu that works with the keyboard, with correct <code>aria-expanded</code> and <code>aria-controls</code>.</li>
            <li>Visible keyboard focus outlines that are never removed without a replacement.</li>
            <li>Form fields with connected labels, required-field marking, and error messages announced with <code>role="alert"</code>.</li>
            <li>Status shown with text (not colour alone), for example request-status badges.</li>
            <li>Images use meaningful alternative text, and a labelled placeholder when a photo is missing.</li>
            <li>Data tables use header cells with <code>scope</code>, and scroll safely inside their own container on small screens.</li>
            <li>The monitoring chart provides a table as its text alternative.</li>
            <li>Three colour themes with readable contrast, and support for the operating-system "reduced motion" preference.</li>
        </ul>

        <h2>Known limitations</h2>
        <p>
            Full, independent WCAG conformance testing has not been carried out,
            so formal compliance is not claimed. Some third-party embedded content
            (for example the OpenStreetMap frame on the <a href="about.php">About</a>
            page) is outside our direct control.
        </p>

        <h2>Reporting a problem</h2>
        <p>
            As a demonstration project, accessibility issues should be reported to
            the project owner or supervising instructor. See the
            <a href="help.php">Help centre</a> for general guidance.
        </p>
    </section>

<?php
require __DIR__ . '/includes/footer.php';
