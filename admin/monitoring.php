<?php
/**
 * AutoVault - Safe, read-only administrator monitoring.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();

$databaseStatus = 'Unavailable';
$databaseServerVersion = 'Unavailable';
$monitoringCounts = [
    'Users' => null,
    'Active vehicles' => null,
    'Favourites' => null,
    'Test-drive requests' => null,
    'Pending requests' => null,
];
$latestDates = [
    'Latest vehicle created' => null,
    'Latest request created' => null,
];
// Aggregate data for the accessible "vehicles by body type" chart.
$vehiclesByBodyType = [];
$localConfigExists = file_exists(__DIR__ . '/../includes/config.php');
$exampleConfigExists = file_exists(__DIR__ . '/../includes/config.example.php');

if ($localConfigExists) {
    try {
        require_once __DIR__ . '/../includes/config.php';

        $dsn = 'mysql:host=' . DB_HOST
            . ';port=' . DB_PORT
            . ';dbname=' . DB_NAME
            . ';charset=' . DB_CHARSET;
        $monitoringPdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $healthStmt = $monitoringPdo->prepare('SELECT 1');
        $healthStmt->execute();
        if ((int) $healthStmt->fetchColumn() === 1) {
            $databaseStatus = 'Connected';
        }

        $versionStmt = $monitoringPdo->prepare('SELECT VERSION()');
        $versionStmt->execute();
        $version = $versionStmt->fetchColumn();
        if (is_string($version) && $version !== '') {
            $databaseServerVersion = $version;
        }

        $countQueries = [
            'Users' => ['SELECT COUNT(*) FROM users', []],
            'Active vehicles' => [
                'SELECT COUNT(*) FROM vehicles WHERE status = :status',
                [':status' => 'available'],
            ],
            'Favourites' => ['SELECT COUNT(*) FROM favourites', []],
            'Test-drive requests' => ['SELECT COUNT(*) FROM test_drive_requests', []],
            'Pending requests' => [
                'SELECT COUNT(*) FROM test_drive_requests WHERE status = :status',
                [':status' => 'pending'],
            ],
        ];

        foreach ($countQueries as $label => $query) {
            $countStmt = $monitoringPdo->prepare($query[0]);
            $countStmt->execute($query[1]);
            $monitoringCounts[$label] = (int) $countStmt->fetchColumn();
        }

        $latestQueries = [
            'Latest vehicle created' => 'SELECT MAX(created_at) FROM vehicles',
            'Latest request created' => 'SELECT MAX(created_at) FROM test_drive_requests',
        ];
        foreach ($latestQueries as $label => $sql) {
            $latestStmt = $monitoringPdo->prepare($sql);
            $latestStmt->execute();
            $latest = $latestStmt->fetchColumn();
            $latestDates[$label] = is_string($latest) && $latest !== '' ? $latest : null;
        }

        // Safe aggregate counts only (no personal data) for the chart below.
        // Empty/NULL body types are grouped under "Unspecified".
        $bodyVizStmt = $monitoringPdo->prepare(
            "SELECT COALESCE(NULLIF(TRIM(body_type), ''), 'Unspecified') AS label,
                    COUNT(*) AS total
             FROM vehicles
             GROUP BY label
             ORDER BY total DESC, label ASC"
        );
        $bodyVizStmt->execute();
        $vehiclesByBodyType = $bodyVizStmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Administrator monitoring database check failed: ' . $e->getMessage());
    }
}

$baseHref = '../';
$pageTitle = 'System Monitoring';
$metaDescription = 'Review safe AutoVault application health information.';
$hideUserName = true;
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page monitoring-page">
        <div class="admin-page__header">
            <div>
                <p><a href="admin/index.php">&larr; Dashboard</a></p>
                <h1>System monitoring</h1>
                <p>Read-only application health information. No personal data or secrets are shown.</p>
            </div>
            <a class="button button-secondary" href="admin/monitoring.php">Refresh</a>
        </div>

        <section aria-labelledby="application-health-heading">
            <h2 id="application-health-heading">Application health</h2>
            <dl class="monitoring-grid">
                <div>
                    <dt>Application</dt>
                    <dd>AutoVault</dd>
                </div>
                <div>
                    <dt>PHP version</dt>
                    <dd><?php echo e(PHP_VERSION); ?></dd>
                </div>
                <div>
                    <dt>Server date and time</dt>
                    <dd><time datetime="<?php echo e(date(DATE_ATOM)); ?>"><?php echo e(date('Y-m-d H:i:s T')); ?></time></dd>
                </div>
                <div>
                    <dt>Database connection</dt>
                    <dd>
                        <span class="status-badge status-badge--<?php echo $databaseStatus === 'Connected' ? 'active' : 'inactive'; ?>">
                            <?php echo e($databaseStatus); ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Database server version</dt>
                    <dd><?php echo e($databaseServerVersion); ?></dd>
                </div>
                <div>
                    <dt>Local configuration</dt>
                    <dd><?php echo $localConfigExists ? 'Present' : 'Missing'; ?></dd>
                </div>
                <div>
                    <dt>Configuration template</dt>
                    <dd><?php echo $exampleConfigExists ? 'Present' : 'Missing'; ?></dd>
                </div>
            </dl>
        </section>

        <section aria-labelledby="record-summary-heading">
            <h2 id="record-summary-heading">Record summary</h2>
            <dl class="monitoring-grid">
                <?php foreach ($monitoringCounts as $label => $count): ?>
                    <div>
                        <dt><?php echo e($label); ?></dt>
                        <dd><?php echo $count === null ? 'Unavailable' : (int) $count; ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </section>

        <section aria-labelledby="latest-activity-heading">
            <h2 id="latest-activity-heading">Latest activity</h2>
            <dl class="monitoring-grid">
                <?php foreach ($latestDates as $label => $value): ?>
                    <div>
                        <dt><?php echo e($label); ?></dt>
                        <dd><?php echo $value === null ? 'No records' : e($value); ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </section>

        <section aria-labelledby="viz-heading" class="viz-section">
            <h2 id="viz-heading">Vehicles by body type</h2>
            <p>A simple visual breakdown of the catalogue using safe aggregate counts.
               The table is the accessible text alternative to the bars.</p>

            <?php if (empty($vehiclesByBodyType)): ?>
                <p class="note">No vehicle data is available to chart yet.</p>
            <?php else: ?>
                <?php
                    // Largest group sets the scale for every bar.
                    $vizMax = 0;
                    foreach ($vehiclesByBodyType as $vizRow) {
                        $vizMax = max($vizMax, (int) $vizRow['total']);
                    }
                    $vizMax = max($vizMax, 1);
                ?>
                <div class="admin-table-wrap">
                    <table class="admin-table viz-table">
                        <caption class="visually-hidden">Number of vehicles grouped by body type</caption>
                        <thead>
                            <tr>
                                <th scope="col">Body type</th>
                                <th scope="col">Vehicles</th>
                                <th scope="col">Relative amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiclesByBodyType as $vizRow): ?>
                                <?php $vizCount = (int) $vizRow['total']; ?>
                                <tr>
                                    <th scope="row"><?php echo e($vizRow['label']); ?></th>
                                    <td><?php echo $vizCount; ?></td>
                                    <td>
                                        <!-- <meter> shows the bar without any inline CSS
                                             and is announced to assistive technology. -->
                                        <meter class="viz-meter" min="0"
                                               max="<?php echo (int) $vizMax; ?>"
                                               value="<?php echo $vizCount; ?>"
                                               aria-label="<?php echo e($vizRow['label'] . ': ' . $vizCount . ' vehicles'); ?>"><?php echo $vizCount; ?></meter>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
