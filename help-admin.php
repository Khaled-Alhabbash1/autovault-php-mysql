<?php
$pageTitle = 'Administrator Help';
$metaDescription = 'Safe overview of AutoVault administrator catalogue and request tools.';
$metaKeywords = 'administrator help, catalogue maintenance, request management';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>
    <article class="help-page">
        <h1>Administrator help</h1>
        <?php render_help_navigation('admin'); ?>

        <section>
            <h2>Access and dashboard</h2>
            <p>
                Administrator tools require an active account with the administrator role.
                Logged-out visitors are redirected and normal users receive a safe access-denied
                response. The dashboard links to vehicles, users, requests, and monitoring.
            </p>
        </section>
        <section>
            <h2>Manage records safely</h2>
            <p>
                Validate the target before editing. Vehicle deactivation marks a record sold
                and preserves relationships. Account and request status actions use protected
                forms. The current and final active administrator safeguards must not be bypassed.
            </p>
        </section>
        <section>
            <h2>Monitoring and maintenance</h2>
            <p>
                Monitoring is read-only and shows safe aggregate health information. It never
                replaces protected server logs or backups. See <code>docs/CONTENT-MAINTENANCE.md</code>
                for image, video, database, backup, and redeployment procedures.
            </p>
        </section>
    </article>
<?php require __DIR__ . '/includes/footer.php'; ?>
