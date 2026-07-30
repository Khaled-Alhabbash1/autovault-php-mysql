<?php
/**
 * AutoVault - Paginated user administration.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$sortOptions = [
    'newest' => 'u.created_at DESC, u.id DESC',
    'oldest' => 'u.created_at ASC, u.id ASC',
    'name_az' => 'u.full_name ASC, u.id ASC',
    'name_za' => 'u.full_name DESC, u.id DESC',
    'email_az' => 'u.email ASC, u.id ASC',
];

$filters = [
    'q' => isset($_GET['q']) && is_string($_GET['q'])
        ? substr(trim($_GET['q']), 0, 150)
        : '',
    'role' => isset($_GET['role']) && is_string($_GET['role'])
        && in_array($_GET['role'], admin_user_roles(), true)
        ? $_GET['role']
        : '',
    'status' => isset($_GET['status']) && is_string($_GET['status'])
        && in_array($_GET['status'], ['active', 'inactive'], true)
        ? $_GET['status']
        : '',
    'date_from' => admin_filter_date($_GET['date_from'] ?? ''),
    'date_to' => admin_filter_date($_GET['date_to'] ?? ''),
    'sort' => isset($_GET['sort']) && is_string($_GET['sort'])
        && isset($sortOptions[$_GET['sort']])
        ? $_GET['sort']
        : 'newest',
];

$where = ['1 = 1'];
$params = [];
if ($filters['q'] !== '') {
    $where[] = '(u.full_name LIKE :q_name OR u.email LIKE :q_email)';
    $params[':q_name'] = '%' . $filters['q'] . '%';
    $params[':q_email'] = '%' . $filters['q'] . '%';
}
if ($filters['role'] !== '') {
    $where[] = 'u.role = :role';
    $params[':role'] = $filters['role'];
}
if ($filters['status'] !== '') {
    $where[] = 'u.is_active = :is_active';
    $params[':is_active'] = $filters['status'] === 'active' ? 1 : 0;
}
if ($filters['date_from'] !== '') {
    $where[] = 'DATE(u.created_at) >= :date_from';
    $params[':date_from'] = $filters['date_from'];
}
if ($filters['date_to'] !== '') {
    $where[] = 'DATE(u.created_at) <= :date_to';
    $params[':date_to'] = $filters['date_to'];
}

$whereSql = implode(' AND ', $where);
$orderBy = $sortOptions[$filters['sort']];
$perPage = 10;
$page = isset($_GET['page']) && is_string($_GET['page'])
    && preg_match('/^[1-9][0-9]*$/', $_GET['page'])
    ? min((int) $_GET['page'], 1000000)
    : 1;
$users = [];
$total = 0;
$totalPages = 1;
$dbError = false;
$flash = take_admin_flash();

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $listStmt = $pdo->prepare(
        "SELECT u.id, u.full_name, u.email, u.role, u.is_active, u.created_at
         FROM users u
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
    $users = $listStmt->fetchAll();
} catch (PDOException $e) {
    error_log('Admin user list query failed: ' . $e->getMessage());
    $dbError = true;
}

$baseHref = '../';
$pageTitle = 'Manage Users';
$metaDescription = 'Search and manage AutoVault user account status.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page">
        <div class="admin-page__header">
            <div>
                <p><a href="admin/index.php">&larr; Dashboard</a></p>
                <h1>Manage users</h1>
            </div>
            <p><?php echo (int) $total; ?> matching account<?php echo $total === 1 ? '' : 's'; ?></p>
        </div>

        <?php if ($flash !== null): ?>
            <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                 role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form class="admin-filter-form" action="admin/users.php" method="get">
            <div class="admin-filter-grid">
                <div class="form-group admin-filter-grid__wide">
                    <label for="user-q">Name or email</label>
                    <input id="user-q" name="q" maxlength="150"
                           value="<?php echo e($filters['q']); ?>">
                </div>
                <div class="form-group">
                    <label for="user-role">Role</label>
                    <select id="user-role" name="role">
                        <option value="">All roles</option>
                        <?php foreach (admin_user_roles() as $role): ?>
                            <option value="<?php echo e($role); ?>"
                                <?php echo $filters['role'] === $role ? 'selected' : ''; ?>>
                                <?php echo e(ucfirst($role)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="user-status">Status</label>
                    <select id="user-status" name="status">
                        <option value="">All statuses</option>
                        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="user-from">Registered from</label>
                    <input type="date" id="user-from" name="date_from"
                           value="<?php echo e($filters['date_from']); ?>">
                </div>
                <div class="form-group">
                    <label for="user-to">Registered to</label>
                    <input type="date" id="user-to" name="date_to"
                           value="<?php echo e($filters['date_to']); ?>">
                </div>
                <div class="form-group">
                    <label for="user-sort">Sort</label>
                    <select id="user-sort" name="sort">
                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest first</option>
                        <option value="oldest" <?php echo $filters['sort'] === 'oldest' ? 'selected' : ''; ?>>Oldest first</option>
                        <option value="name_az" <?php echo $filters['sort'] === 'name_az' ? 'selected' : ''; ?>>Name A–Z</option>
                        <option value="name_za" <?php echo $filters['sort'] === 'name_za' ? 'selected' : ''; ?>>Name Z–A</option>
                        <option value="email_az" <?php echo $filters['sort'] === 'email_az' ? 'selected' : ''; ?>>Email A–Z</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="button" type="submit">Apply filters</button>
                <a class="button button-secondary" href="admin/users.php">Clear filters</a>
            </div>
        </form>

        <?php if ($dbError): ?>
            <div class="form-errors" role="alert">We could not load users right now.</div>
        <?php elseif (!$users): ?>
            <div class="no-results"><h2>No users found</h2></div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <caption class="visually-hidden">AutoVault user accounts and status actions</caption>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Registered</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $isActive = (int) $user['is_active'] === 1; ?>
                            <tr>
                                <td><?php echo (int) $user['id']; ?></td>
                                <td><?php echo e($user['full_name']); ?></td>
                                <td><?php echo e($user['email']); ?></td>
                                <td><?php echo e(ucfirst($user['role'])); ?></td>
                                <td>
                                    <span class="status-badge status-badge--<?php echo $isActive ? 'active' : 'inactive'; ?>">
                                        <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo e(date('M j, Y', strtotime($user['created_at']))); ?></td>
                                <td>
                                    <?php if ((int) $user['id'] === (int) current_user()['id']): ?>
                                        <span class="admin-muted">Current account</span>
                                    <?php else: ?>
                                        <form action="admin/user-status.php" method="post">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                            <input type="hidden" name="action"
                                                   value="<?php echo $isActive ? 'deactivate' : 'activate'; ?>">
                                            <button class="button button-small<?php echo $isActive ? ' button-danger' : ''; ?>"
                                                    type="submit">
                                                <?php echo $isActive ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="User pages">
                    <?php for ($number = 1; $number <= $totalPages; $number++): ?>
                        <?php if ($number === $page): ?>
                            <span class="pagination__link pagination__link--current"
                                  aria-current="page"><?php echo $number; ?></span>
                        <?php else: ?>
                            <a class="pagination__link"
                               href="<?php echo e(admin_page_url('admin/users.php', $filters, $number)); ?>">
                                <?php echo $number; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
