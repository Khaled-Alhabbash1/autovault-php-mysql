<?php
/**
 * AutoVault - Small administrator dashboard.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$counts = [
    'Vehicles' => 0,
    'Available vehicles' => 0,
    'Users' => 0,
    'Pending requests' => 0,
];
$dbError = false;

try {
    $countQueries = [
        'Vehicles' => [
            'sql' => 'SELECT COUNT(*) FROM vehicles',
            'params' => [],
        ],
        'Available vehicles' => [
            'sql' => 'SELECT COUNT(*) FROM vehicles WHERE status = :status',
            'params' => [':status' => 'available'],
        ],
        'Users' => [
            'sql' => 'SELECT COUNT(*) FROM users',
            'params' => [],
        ],
        'Pending requests' => [
            'sql' => 'SELECT COUNT(*) FROM test_drive_requests WHERE status = :status',
            'params' => [':status' => 'pending'],
        ],
    ];

    foreach ($countQueries as $label => $query) {
        $countStmt = $pdo->prepare($query['sql']);
        $countStmt->execute($query['params']);
        $counts[$label] = (int) $countStmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log('Admin dashboard query failed: ' . $e->getMessage());
    $dbError = true;
}

$baseHref = '../';
$pageTitle = 'Administrator Dashboard';
$metaDescription = 'AutoVault vehicle administration.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="vehicle-details__eyebrow">Administration</p>
                <h1>Vehicle dashboard</h1>
            </div>
            <a class="button" href="admin/vehicle-create.php">Add vehicle</a>
        </div>

        <?php if ($dbError): ?>
            <div class="form-errors" role="alert">
                We could not load vehicle totals right now.
            </div>
        <?php else: ?>
            <dl class="admin-counts">
                <?php foreach ($counts as $label => $count): ?>
                    <div>
                        <dt><?php echo e($label); ?></dt>
                        <dd><?php echo (int) $count; ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <div class="admin-panel-grid">
            <div class="admin-panel">
                <h2>Vehicle management</h2>
                <p>Create vehicles, update catalogue details, or deactivate them safely.</p>
                <a class="button button-secondary" href="admin/vehicles.php">Manage vehicles</a>
            </div>
            <div class="admin-panel">
                <h2>User administration</h2>
                <p>Search accounts and safely manage active status.</p>
                <a class="button button-secondary" href="admin/users.php">Manage users</a>
            </div>
            <div class="admin-panel">
                <h2>Test-drive requests</h2>
                <p>Review request details and update workflow status.</p>
                <a class="button button-secondary" href="admin/testdrives.php">Manage requests</a>
            </div>
            <div class="admin-panel">
                <h2>System monitoring</h2>
                <p>Review safe application, database, and record-count health information.</p>
                <a class="button button-secondary" href="admin/monitoring.php">View monitoring</a>
            </div>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
