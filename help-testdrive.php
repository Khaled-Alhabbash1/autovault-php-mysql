<?php
$pageTitle = 'Test-Drive Help';
$metaDescription = 'Help submitting and understanding AutoVault test-drive requests.';
$metaKeywords = 'test drive help, vehicle appointment request';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>
    <article class="help-page">
        <h1>Test-drive help</h1>
        <?php render_help_navigation('testdrive'); ?>

        <section>
            <h2>Submit a request</h2>
            <p>
                Log in, open an available vehicle, and choose <strong>Request a test drive</strong>.
                Select a future preferred date. Time, telephone, and message are optional.
                Your name and email come from the signed-in account.
            </p>
        </section>
        <section>
            <h2>Validation and duplicate requests</h2>
            <p>
                Dates in the past, invalid phone values, oversized messages, and unavailable
                vehicles are rejected. AutoVault also prevents another active request for the
                same account, vehicle, and date.
            </p>
        </section>
        <section>
            <h2>Request status</h2>
            <p>
                A submitted request begins as pending and is not a confirmed appointment.
                An administrator may later mark it confirmed, completed, or cancelled.
                The current site does not send email or SMS notifications.
            </p>
        </section>
    </article>
<?php require __DIR__ . '/includes/footer.php'; ?>
