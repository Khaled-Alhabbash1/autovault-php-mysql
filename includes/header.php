<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Reusable page header
 * -------------------------------------------------------------------------
 * Every page includes this file at the very top. It opens the HTML document,
 * adds SEO meta tags, links the stylesheet, and shows the site navigation.
 *
 * A page can set these variables BEFORE including this file to customise it:
 *     $pageTitle       = 'About Us';                 // shown in the browser tab
 *     $metaDescription = 'Learn about AutoVault.';   // used by search engines
 *
 * If a page does not set them, sensible defaults are used below.
 * -------------------------------------------------------------------------
 */

// The name of the site, shown in the logo and titles.
$siteName = 'AutoVault';

// Use the page's title/description if it set one, otherwise use a default.
$pageTitle       = isset($pageTitle) ? $pageTitle : $siteName;
$metaDescription = isset($metaDescription)
    ? $metaDescription
    : 'AutoVault is a vehicle marketplace to browse cars, save favourites and request test drives.';

// Work out which page is open so we can highlight the active menu link.
$currentPage = basename($_SERVER['PHP_SELF']);

/**
 * Small helper: prints ' active' when the given file is the current page.
 * Used to add the "active" CSS class to the current navigation link.
 */
function navActive($file, $currentPage) {
    return ($file === $currentPage) ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Character set and responsive viewport (needed for mobile layouts) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO meta tags. htmlspecialchars() keeps any special characters safe. -->
    <title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo htmlspecialchars($siteName); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">

    <!-- External stylesheet (no CSS frameworks are used) -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Site header with logo and responsive navigation menu -->
    <header class="site-header">
        <div class="container header-inner">

            <!-- Logo / site name links back to the home page -->
            <a class="logo" href="index.php"><?php echo htmlspecialchars($siteName); ?></a>

            <!-- Button shown on small screens to open/close the menu.
                 The JavaScript in app.js toggles the menu when it is clicked. -->
            <button class="nav-toggle" id="navToggle"
                    aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
            </button>

            <!-- Main navigation. On mobile it is hidden until the button opens it. -->
            <nav class="main-nav" id="mainNav" aria-label="Main navigation">
                <ul class="nav-menu">
                    <li><a class="nav-link<?php echo navActive('index.php', $currentPage); ?>" href="index.php">Home</a></li>
                    <li><a class="nav-link<?php echo navActive('catalogue.php', $currentPage); ?>" href="catalogue.php">Catalogue</a></li>
                    <li><a class="nav-link<?php echo navActive('about.php', $currentPage); ?>" href="about.php">About</a></li>
                    <li><a class="nav-link<?php echo navActive('login.php', $currentPage); ?>" href="login.php">Login</a></li>
                    <li><a class="nav-link<?php echo navActive('register.php', $currentPage); ?>" href="register.php">Register</a></li>
                </ul>
            </nav>

        </div>
    </header>

    <!-- Page content starts here. Each page closes <main> in footer.php. -->
    <main class="container main-content">
