<?php
/**
 * AutoVault - Searchable, paginated test-drive request administration.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$sortOptions = [
    'newest' => 'r.created_at DESC, r.id DESC',
    'oldest' => 'r.created_at ASC, r.id ASC',
    'date_soonest' => '(r.preferred_date IS NULL), r.preferred_date ASC, r.id DESC',
    'date_latest' => '(r.preferred_date IS NULL), r.preferred_date DESC, r.id DESC',
    'status' => 'r.status ASC, r.created_at DESC',
    'make_az' => 'v.make ASC, v.model ASC, r.id DESC',
];

$filters = [
    'q' => isset($_GET['q']) && is_string($_GET['q'])
        ? substr(trim($_GET['q']), 0, 150)
        : '',
    'status' => isset($_GET['status']) && is_string($_GET['status'])
        && in_array($_GET['status'], admin_request_statuses(), true)
        ? $_GET['status']
        : '',
    'make' => isset($_GET['make']) && is_string($_GET['make'])
        ? substr(trim($_GET['make']), 0, 50)
        : '',
    'preferred_from' => admin_filter_date($_GET['preferred_from'] ?? ''),
    'preferred_to' => admin_filter_date($_GET['preferred_to'] ?? ''),
    'submitted_from' => admin_filter_date($_GET['submitted_from'] ?? ''),
    'submitted_to' => admin_filter_date($_GET['submitted_to'] ?? ''),
    'sort' => isset($_GET['sort']) && is_string($_GET['sort'])
        && isset($sortOptions[$_GET['sort']])
        ? $_GET['sort']
        : 'newest',
];

$where = ['1 = 1'];
$params = [];
if ($filters['q'] !== '') {
    $where[] = '(COALESCE(u.full_name, r.full_name) LIKE :q_name
                 OR COALESCE(u.email, r.email) LIKE :q_email)';
    $params[':q_name'] = '%' . $filters['q'] . '%';
    $params[':q_email'] = '%' . $filters['q'] . '%';
}
if ($filters['status'] !== '') {
    $where[] = 'r.status = :status';
    $params[':status'] = $filters['status'];
}
if ($filters['make'] !== '') {
    $where[] = 'v.make = :make';
    $params[':make'] = $filters['make'];
}
if ($filters['preferred_from'] !== '') {
    $where[] = 'r.preferred_date >= :preferred_from';
    $params[':preferred_from'] = $filters['preferred_from'];
}
if ($filters['preferred_to'] !== '') {
    $where[] = 'r.preferred_date <= :preferred_to';
    $params[':preferred_to'] = $filters['preferred_to'];
}
if ($filters['submitted_from'] !== '') {
    $where[] = 'DATE(r.created_at) >= :submitted_from';
    $params[':submitted_from'] = $filters['submitted_from'];
}
if ($filters['submitted_to'] !== '') {
    $where[] = 'DATE(r.created_at) <= :submitted_to';
    $params[':submitted_to'] = $filters['submitted_to'];
}

$whereSql = implode(' AND ', $where);
$orderBy = $sortOptions[$filters['sort']];
$perPage = 10;
$page = isset($_GET['page']) && is_string($_GET['page'])
    && preg_match('/^[1-9][0-9]*$/', $_GET['page'])
    ? min((int) $_GET['page'], 1000000)
    : 1;
$requests = [];
$total = 0;
$totalPages = 1;
$dbError = false;
$flash = take_admin_flash();

try {
    $countStmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM test_drive_requests r
         LEFT JOIN users u ON u.id = r.user_id
         LEFT JOIN vehicles v ON v.id = r.vehicle_id
         WHERE $whereSql"
    );
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $listStmt = $pdo->prepare(
        "SELECT r.id, r.vehicle_id,
                COALESCE(u.full_name, r.full_name) AS display_name,
                COALESCE(u.email, r.email) AS display_email,
                r.phone,
                r.preferred_date, r.preferred_time, r.status, r.created_at,
                v.year, v.make, v.model
         FROM test_drive_requests r
         LEFT JOIN users u ON u.id = r.user_id
         LEFT JOIN vehicles v ON v.id = r.vehicle_id
         WHERE $whereSql
         ORDER BY $orderBy
         LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $placeholder => $value) {
        $listStmt->bindValue($placeholder, $value);
    }
    $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $requests = $listStmt->fetchAll();
} catch (PDOException $e) {
    error_log('Admin test-drive list query failed: ' . $e->getMessage());
    $dbError = true;
}

$baseHref = '../';
$pageTitle = 'Test-Drive Requests';
$metaDescription = 'Search and manage AutoVault test-drive requests.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page">
        <div class="admin-page__header">
            <div>
                <p><a href="admin/index.php">&larr; Dashboard</a></p>
                <h1>Test-drive requests</h1>
            </div>
            <p><?php echo (int) $total; ?> matching request<?php echo $total === 1 ? '' : 's'; ?></p>
        </div>

        <?php if ($flash !== null): ?>
            <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                 role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form class="admin-filter-form" action="admin/testdrives.php" method="get">
            <div class="admin-filter-grid">
                <div class="form-group admin-filter-grid__wide">
                    <label for="request-q">User name or email</label>
                    <input id="request-q" name="q" maxlength="150"
                           value="<?php echo e($filters['q']); ?>">
                </div>
                <div class="form-group">
                    <label for="request-status">Status</label>
                    <select id="request-status" name="status">
                        <option value="">All statuses</option>
                        <?php foreach (admin_request_statuses() as $status): ?>
                            <option value="<?php echo e($status); ?>"
                                <?php echo $filters['status'] === $status ? 'selected' : ''; ?>>
                                <?php echo e(ucfirst($status)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="request-make">Vehicle make</label>
                    <input id="request-make" name="make" maxlength="50"
                           value="<?php echo e($filters['make']); ?>">
                </div>
                <div class="form-group">
                    <label for="preferred-from">Preferred from</label>
                    <input type="date" id="preferred-from" name="preferred_from"
                           value="<?php echo e($filters['preferred_from']); ?>">
                </div>
                <div class="form-group">
                    <label for="preferred-to">Preferred to</label>
                    <input type="date" id="preferred-to" name="preferred_to"
                           value="<?php echo e($filters['preferred_to']); ?>">
                </div>
                <div class="form-group">
                    <label for="submitted-from">Submitted from</label>
                    <input type="date" id="submitted-from" name="submitted_from"
                           value="<?php echo e($filters['submitted_from']); ?>">
                </div>
                <div class="form-group">
                    <label for="submitted-to">Submitted to</label>
                    <input type="date" id="submitted-to" name="submitted_to"
                           value="<?php echo e($filters['submitted_to']); ?>">
                </div>
                <div class="form-group">
                    <label for="request-sort">Sort</label>
                    <select id="request-sort" name="sort">
                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest submitted</option>
                        <option value="oldest" <?php echo $filters['sort'] === 'oldest' ? 'selected' : ''; ?>>Oldest submitted</option>
                        <option value="date_soonest" <?php echo $filters['sort'] === 'date_soonest' ? 'selected' : ''; ?>>Preferred date soonest</option>
                        <option value="date_latest" <?php echo $filters['sort'] === 'date_latest' ? 'selected' : ''; ?>>Preferred date latest</option>
                        <option value="status" <?php echo $filters['sort'] === 'status' ? 'selected' : ''; ?>>Status</option>
                        <option value="make_az" <?php echo $filters['sort'] === 'make_az' ? 'selected' : ''; ?>>Vehicle make A–Z</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="button" type="submit">Apply filters</button>
                <a class="button button-secondary" href="admin/testdrives.php">Clear filters</a>
            </div>
        </form>

        <?php if ($dbError): ?>
            <div class="form-errors" role="alert">We could not load requests right now.</div>
        <?php elseif (!$requests): ?>
            <div class="no-results"><h2>No requests found</h2></div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <caption class="visually-hidden">AutoVault test-drive requests and detail links</caption>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">User</th>
                            <th scope="col">Vehicle</th>
                            <th scope="col">Preferred</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Status</th>
                            <th scope="col">Submitted</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo (int) $request['id']; ?></td>
                                <td>
                                    <?php echo e($request['display_name']); ?><br>
                                    <span class="admin-muted"><?php echo e($request['display_email']); ?></span>
                                </td>
                                <td>
                                    <?php if ($request['vehicle_id'] !== null && $request['make'] !== null): ?>
                                        <a href="vehicle.php?id=<?php echo (int) $request['vehicle_id']; ?>">
                                            <?php echo e($request['year'] . ' ' . $request['make'] . ' ' . $request['model']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="admin-muted">Vehicle unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $request['preferred_date'] !== null
                                        ? e($request['preferred_date'])
                                        : 'Not specified'; ?>
                                    <?php if ($request['preferred_time'] !== null): ?>
                                        <br><span class="admin-muted"><?php echo e(substr($request['preferred_time'], 0, 5)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $request['phone'] !== null ? e($request['phone']) : '—'; ?></td>
                                <td>
                                    <span class="status-badge status-badge--<?php echo e($request['status']); ?>">
                                        <?php echo e(ucfirst($request['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo e(date('M j, Y', strtotime($request['created_at']))); ?></td>
                                <td>
                                    <a href="admin/testdrive-view.php?id=<?php echo (int) $request['id']; ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Test-drive request pages">
                    <?php for ($number = 1; $number <= $totalPages; $number++): ?>
                        <?php if ($number === $page): ?>
                            <span class="pagination__link pagination__link--current"
                                  aria-current="page"><?php echo $number; ?></span>
                        <?php else: ?>
                            <a class="pagination__link"
                               href="<?php echo e(admin_page_url('admin/testdrives.php', $filters, $number)); ?>">
                                <?php echo $number; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
