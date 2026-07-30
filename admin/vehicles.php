<?php
/**
 * AutoVault - Paginated administrator vehicle list.
 */

require_once __DIR__ . '/../includes/admin-functions.php';
require_admin_page();
require_once __DIR__ . '/../includes/db.php';

$perPage = 10;
$page = 1;
if (isset($_GET['page']) && is_string($_GET['page']) && preg_match('/^[1-9][0-9]*$/', $_GET['page'])) {
    $page = min((int) $_GET['page'], 1000000);
}

$vehicles = [];
$total = 0;
$totalPages = 1;
$dbError = false;
$flash = take_admin_flash();

try {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM vehicles');
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $listStmt = $pdo->prepare(
        'SELECT id, make, model, year, price, mileage, status, is_featured
         FROM vehicles
         ORDER BY created_at DESC, id DESC
         LIMIT :limit OFFSET :offset'
    );
    $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $vehicles = $listStmt->fetchAll();
} catch (PDOException $e) {
    error_log('Admin vehicle list query failed: ' . $e->getMessage());
    $dbError = true;
}

$baseHref = '../';
$pageTitle = 'Manage Vehicles';
$metaDescription = 'Manage AutoVault catalogue vehicles.';
require __DIR__ . '/../includes/header.php';
?>

    <section class="admin-page">
        <div class="admin-page__header">
            <div>
                <p><a href="admin/index.php">&larr; Dashboard</a></p>
                <h1>Manage vehicles</h1>
            </div>
            <a class="button" href="admin/vehicle-create.php">Add vehicle</a>
        </div>

        <?php if ($flash !== null): ?>
            <div class="<?php echo $flash['type'] === 'error' ? 'form-errors' : 'form-success'; ?>"
                 role="<?php echo $flash['type'] === 'error' ? 'alert' : 'status'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($dbError): ?>
            <div class="form-errors" role="alert">We could not load the vehicle list.</div>
        <?php elseif (!$vehicles): ?>
            <div class="no-results">
                <h2>No vehicles yet</h2>
                <p>Create the first catalogue vehicle when you are ready.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <caption class="visually-hidden">AutoVault vehicle inventory and management actions</caption>
                    <thead>
                        <tr>
                            <th scope="col">Vehicle</th>
                            <th scope="col">Year</th>
                            <th scope="col">Price</th>
                            <th scope="col">Mileage</th>
                            <th scope="col">Status</th>
                            <th scope="col">Featured</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?php echo e($vehicle['make'] . ' ' . $vehicle['model']); ?></td>
                                <td><?php echo e($vehicle['year']); ?></td>
                                <td><?php echo e(format_price($vehicle['price'])); ?></td>
                                <td><?php echo e(format_mileage($vehicle['mileage'])); ?></td>
                                <td><?php echo e(ucfirst($vehicle['status'])); ?></td>
                                <td><?php echo (int) $vehicle['is_featured'] === 1 ? 'Yes' : 'No'; ?></td>
                                <td class="admin-table__actions">
                                    <a href="admin/vehicle-edit.php?id=<?php echo (int) $vehicle['id']; ?>">Edit</a>
                                    <a href="admin/vehicle-delete.php?id=<?php echo (int) $vehicle['id']; ?>">Deactivate</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Vehicle list pages">
                    <?php for ($number = 1; $number <= $totalPages; $number++): ?>
                        <?php if ($number === $page): ?>
                            <span class="pagination__link pagination__link--current"
                                  aria-current="page"><?php echo $number; ?></span>
                        <?php else: ?>
                            <a class="pagination__link"
                               href="admin/vehicles.php?page=<?php echo $number; ?>">
                                <?php echo $number; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
