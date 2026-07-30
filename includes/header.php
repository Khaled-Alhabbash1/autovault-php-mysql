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

// Load the session/auth helper. This safely starts the session (in one
// reusable place) so the navigation below can show login-aware links.
require_once __DIR__ . '/auth.php';

// The name of the site, shown in the logo and titles.
$siteName = 'AutoVault';

// Use the page's title/description if it set one, otherwise use a default.
$pageTitle       = isset($pageTitle) ? $pageTitle : $siteName;
$metaDescription = isset($metaDescription)
    ? $metaDescription
    : 'AutoVault is a vehicle marketplace to browse cars, save favourites and request test drives.';
$metaKeywords = isset($metaKeywords)
    ? $metaKeywords
    : 'vehicle marketplace, used vehicles, favourites, test drives, AutoVault';

// Work out which page is open so we can highlight the active menu link.
$currentPage = basename($_SERVER['PHP_SELF']);
$normalisedPath = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$inAdminArea = strpos($normalisedPath, '/admin/') !== false;
$robots = isset($robots)
    ? $robots
    : ($inAdminArea ? 'noindex, nofollow' : 'index, follow');
$openGraphTitle = isset($openGraphTitle) ? $openGraphTitle : $pageTitle . ' | ' . $siteName;
$openGraphDescription = isset($openGraphDescription)
    ? $openGraphDescription
    : $metaDescription;

// The Help link follows the current workflow without exposing private data.
$helpHref = 'help.php';
$contextHelpPages = [
    'catalogue.php' => 'help-catalogue.php',
    'vehicle.php' => 'help-catalogue.php',
    'login.php' => 'help-account.php',
    'register.php' => 'help-account.php',
    'favourites.php' => 'help-favourites.php',
    'testdrive.php' => 'help-testdrive.php',
    'media.php' => 'help-media.php',
];
if ($inAdminArea) {
    $helpHref = 'help-admin.php';
} elseif (isset($contextHelpPages[$currentPage])) {
    $helpHref = $contextHelpPages[$currentPage];
}
$helpIsActive = strpos($currentPage, 'help') === 0;

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

    <?php if (isset($baseHref)): ?>
        <!-- Admin pages live in a subfolder; this keeps shared asset/navigation paths rooted. -->
        <base href="<?php echo e($baseHref); ?>">
    <?php endif; ?>

    <!-- SEO meta tags. htmlspecialchars() keeps any special characters safe. -->
    <title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo htmlspecialchars($siteName); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="<?php echo e($metaKeywords); ?>">
    <meta name="robots" content="<?php echo e($robots); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e($openGraphTitle); ?>">
    <meta property="og:description" content="<?php echo e($openGraphDescription); ?>">
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">

    <!-- Loaded before the stylesheet so the saved/system theme is applied early. -->
    <script src="assets/js/app.js"></script>

    <!-- External stylesheet (no CSS frameworks are used) -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <a class="skip-link" href="#main-content">Skip to main content</a>

    <!-- Site header with logo and responsive navigation menu -->
    <header class="site-header">
        <div class="container header-inner">

            <!-- Logo / site name links back to the home page -->
            <a class="logo" href="index.php"><?php echo htmlspecialchars($siteName); ?></a>

            <!-- Button shown on small screens to open/close the menu.
                 The JavaScript in app.js toggles the menu when it is clicked. -->
            <button class="nav-toggle" id="navToggle" type="button"
                    aria-label="Toggle navigation menu" aria-expanded="false"
                    aria-controls="mainNav">
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
            </button>

            <!-- Main navigation. On mobile it is hidden until the button opens it. -->
            <nav class="main-nav" id="mainNav" aria-label="Main navigation">
                <ul class="nav-menu">
                    <!-- These public links are always shown. -->
                    <li><a class="nav-link<?php echo $inAdminArea ? '' : navActive('index.php', $currentPage); ?>" href="index.php">Home</a></li>
                    <li><a class="nav-link<?php echo navActive('catalogue.php', $currentPage); ?>" href="catalogue.php">Catalogue</a></li>
                    <li><a class="nav-link<?php echo navActive('media.php', $currentPage); ?>" href="media.php">Media</a></li>
                    <li><a class="nav-link<?php echo navActive('about.php', $currentPage); ?>" href="about.php">About</a></li>
                    <li><a class="nav-link<?php echo $helpIsActive ? ' active' : ''; ?>"
                           href="<?php echo e($helpHref); ?>">Help</a></li>
                    <li class="nav-theme">
                        <!-- Three clearly labelled theme buttons. JavaScript marks
                             the active one with aria-pressed and an "is-active"
                             class. Real <button> elements keep it keyboard usable. -->
                        <div class="theme-switch" role="group" aria-label="Colour theme">
                            <span class="theme-switch__label" aria-hidden="true">Theme</span>
                            <button type="button" class="theme-switch__option"
                                    data-theme-value="light" aria-pressed="false">Light</button>
                            <button type="button" class="theme-switch__option"
                                    data-theme-value="dark" aria-pressed="false">Dark</button>
                            <button type="button" class="theme-switch__option"
                                    data-theme-value="showroom" aria-pressed="false">Showroom</button>
                        </div>
                    </li>

                    <?php if (is_logged_in()): ?>
                        <!-- Logged-in visitors: a welcome message and a Logout button.
                             The name is escaped with e() to keep output safe. -->
                        <li><a class="nav-link<?php echo navActive('favourites.php', $currentPage); ?>" href="favourites.php">Favourites</a></li>
                        <?php if (is_admin()): ?>
                            <li><a class="nav-link<?php echo $inAdminArea ? ' active' : ''; ?>" href="admin/index.php">Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-welcome">
                            <?php if (!empty($hideUserName)): ?>
                                Signed in
                            <?php else: ?>
                                Hi, <?php echo e(current_user()['name']); ?>
                            <?php endif; ?>
                        </li>
                        <li>
                            <!-- Logout is a POST form carrying a CSRF token, so it
                                 cannot be triggered by another website. -->
                            <form action="logout.php" method="post" class="logout-form">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="nav-link nav-logout">Logout</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <!-- Logged-out visitors: Login and Register. -->
                        <li><a class="nav-link<?php echo navActive('login.php', $currentPage); ?>" href="login.php">Login</a></li>
                        <li><a class="nav-link<?php echo navActive('register.php', $currentPage); ?>" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </header>

    <!-- Page content starts here. Each page closes <main> in footer.php. -->
    <main class="container main-content" id="main-content" tabindex="-1">
