<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Reusable page footer
 * -------------------------------------------------------------------------
 * Every page includes this file at the very bottom. It closes the <main>
 * content area, shows the footer, loads the JavaScript, and closes the
 * HTML document.
 * -------------------------------------------------------------------------
 */

// Site name (same value used in the header).
$siteName = isset($siteName) ? $siteName : 'AutoVault';

// Current year, shown in the copyright line (updates automatically).
$currentYear = date('Y');
?>
    </main><!-- .main-content (opened in header.php) -->

    <!-- Site footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <p class="footer-brand"><?php echo htmlspecialchars($siteName); ?></p>
            <nav aria-label="Footer navigation">
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="catalogue.php">Catalogue</a></li>
                </ul>
            </nav>
            <p class="copyright">
                &copy; <?php echo htmlspecialchars($currentYear); ?>
                <?php echo htmlspecialchars($siteName); ?>. University project.
            </p>
        </div>
    </footer>

    <!-- External JavaScript (no JS frameworks are used) -->
    <script src="assets/js/app.js"></script>
</body>
</html>
