<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Public vehicle catalogue
 * -------------------------------------------------------------------------
 * Shows available vehicles with search, filtering, sorting and pagination.
 * This page is PUBLIC - no login is required.
 *
 * Security notes for beginners:
 *   - Every database query uses a PDO prepared statement.
 *   - Filter values are validated in includes/vehicle-functions.php and bound
 *     with NAMED placeholders - raw GET values never go into the SQL text.
 *   - Sorting only ever uses a value from a fixed whitelist.
 *   - LIMIT/OFFSET use validated integers.
 *   - All output is escaped with e() (htmlspecialchars).
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/db.php';               // gives us $pdo
require_once __DIR__ . '/includes/vehicle-functions.php'; // helper functions

// How many vehicles to show per page.
$perPage = 9;

// 1. Build the search/filter WHERE clause from the URL (validated + safe).
$built    = build_catalogue_filters($_GET);
$whereSql = implode(' AND ', $built['where']);
$params   = $built['params'];
$filters  = $built['filters'];

// 2. Work out the sort order from the whitelist (default: newest first).
$sortOptions = catalogue_sort_options();
$sortKey     = (string) ($_GET['sort'] ?? 'newest');
if (!isset($sortOptions[$sortKey])) {
    $sortKey = 'newest';
}
$orderBy = $sortOptions[$sortKey]['sql']; // a trusted, fixed string

// 3. Read the current page (must be a positive whole number).
$page = 1;
if (isset($_GET['page']) && preg_match('/^\d+$/', (string) $_GET['page'])) {
    $page = max(1, (int) $_GET['page']);
}

// Values filled in by the queries below.
$dbError    = false;
$vehicles   = [];
$total      = 0;
$totalPages = 1;
$makes      = [];
$bodyTypes  = [];

try {
    // --- Dropdown data: the distinct makes and body types in stock ---
    // (Static SQL, no user input, but still run as prepared statements.)
    $makeStmt = $pdo->prepare(
        "SELECT DISTINCT make FROM vehicles
         WHERE status = 'available' AND make <> ''
         ORDER BY make"
    );
    $makeStmt->execute();
    $makes = $makeStmt->fetchAll(PDO::FETCH_COLUMN);

    $bodyStmt = $pdo->prepare(
        "SELECT DISTINCT body_type FROM vehicles
         WHERE status = 'available' AND body_type IS NOT NULL AND body_type <> ''
         ORDER BY body_type"
    );
    $bodyStmt->execute();
    $bodyTypes = $bodyStmt->fetchAll(PDO::FETCH_COLUMN);

    // --- COUNT query: how many vehicles match the current filters? ---
    // Uses the SAME $whereSql and $params as the main query below.
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // --- Pagination maths ---
    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;            // never go past the last page
    }
    $offset = ($page - 1) * $perPage;   // validated integers (safe for SQL)

    // --- Main query: fetch this page of vehicles + their primary image ---
    // The LEFT JOIN picks the single primary image (falling back to the first)
    // for each vehicle, so we do not need one query per vehicle.
    $sql = "SELECT v.id, v.make, v.model, v.year, v.price, v.mileage,
                   v.body_type, v.transmission, v.fuel_type, v.status,
                   pi.image_path, pi.alt_text
            FROM vehicles v
            LEFT JOIN vehicle_images pi ON pi.id = (
                SELECT id FROM vehicle_images
                WHERE vehicle_id = v.id
                ORDER BY is_primary DESC, sort_order ASC, id ASC
                LIMIT 1
            )
            WHERE $whereSql
            ORDER BY $orderBy
            LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();

} catch (PDOException $e) {
    // Log the real reason for us, show a friendly message to the visitor.
    error_log('Catalogue query failed: ' . $e->getMessage());
    $dbError    = true;
    $vehicles   = [];
    $total      = 0;
    $totalPages = 1;
    $page       = 1;
}

/**
 * Build a catalogue URL that keeps the current filters and sort, and sets a
 * given page number. Used for the pagination links.
 */
function catalogue_page_url(array $filters, $sortKey, $pageNumber) {
    $query = [];
    foreach ($filters as $key => $value) {
        if ($value !== '') {
            $query[$key] = $value;
        }
    }
    if ($sortKey !== 'newest') {
        $query['sort'] = $sortKey;
    }
    $query['page'] = $pageNumber;
    return 'catalogue.php?' . http_build_query($query);
}

// ---- Display the page ----
$pageTitle       = 'Catalogue';
$metaDescription = 'Browse available vehicles at AutoVault. Search and filter by make, price, year and more.';
require __DIR__ . '/includes/header.php';
?>

    <section class="catalogue">
        <h1>Vehicle Catalogue</h1>

        <?php if ($dbError): ?>
            <!-- Shown only if a database error happened (details are logged, not shown). -->
            <div class="form-errors" role="alert">
                Sorry, we could not load the catalogue right now. Please try again later.
            </div>
        <?php endif; ?>

        <!-- Search + filter form. Uses GET so results are shareable/bookmarkable. -->
        <form class="filter-form" action="catalogue.php" method="get">

            <div class="filter-grid">

                <div class="form-group filter-keyword">
                    <label for="q">Search make or model</label>
                    <input type="text" id="q" name="q"
                           value="<?php echo e($filters['q']); ?>"
                           placeholder="e.g. Toyota Corolla" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="make">Make</label>
                    <select id="make" name="make">
                        <option value="">Any make</option>
                        <?php foreach ($makes as $makeOption): ?>
                            <option value="<?php echo e($makeOption); ?>"
                                <?php echo ($filters['make'] === $makeOption) ? 'selected' : ''; ?>>
                                <?php echo e($makeOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="body_type">Body type</label>
                    <select id="body_type" name="body_type">
                        <option value="">Any body type</option>
                        <?php foreach ($bodyTypes as $bodyOption): ?>
                            <option value="<?php echo e($bodyOption); ?>"
                                <?php echo ($filters['body_type'] === $bodyOption) ? 'selected' : ''; ?>>
                                <?php echo e($bodyOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission">
                        <option value="">Any</option>
                        <?php foreach (catalogue_transmissions() as $option): ?>
                            <option value="<?php echo e($option); ?>"
                                <?php echo ($filters['transmission'] === $option) ? 'selected' : ''; ?>>
                                <?php echo e($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fuel_type">Fuel type</label>
                    <select id="fuel_type" name="fuel_type">
                        <option value="">Any</option>
                        <?php foreach (catalogue_fuel_types() as $option): ?>
                            <option value="<?php echo e($option); ?>"
                                <?php echo ($filters['fuel_type'] === $option) ? 'selected' : ''; ?>>
                                <?php echo e($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="min_year">Min year</label>
                    <input type="number" id="min_year" name="min_year"
                           value="<?php echo e($filters['min_year']); ?>"
                           min="1900" max="2100" step="1">
                </div>

                <div class="form-group">
                    <label for="max_year">Max year</label>
                    <input type="number" id="max_year" name="max_year"
                           value="<?php echo e($filters['max_year']); ?>"
                           min="1900" max="2100" step="1">
                </div>

                <div class="form-group">
                    <label for="min_price">Min price</label>
                    <input type="number" id="min_price" name="min_price"
                           value="<?php echo e($filters['min_price']); ?>"
                           min="0" step="1">
                </div>

                <div class="form-group">
                    <label for="max_price">Max price</label>
                    <input type="number" id="max_price" name="max_price"
                           value="<?php echo e($filters['max_price']); ?>"
                           min="0" step="1">
                </div>

                <div class="form-group">
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        <?php foreach ($sortOptions as $key => $option): ?>
                            <option value="<?php echo e($key); ?>"
                                <?php echo ($sortKey === $key) ? 'selected' : ''; ?>>
                                <?php echo e($option['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="filter-actions">
                <button type="submit" class="button">Search</button>
                <!-- Clearing filters is just a link back to the plain catalogue. -->
                <a class="button button-secondary" href="catalogue.php">Clear filters</a>
            </div>
        </form>

        <?php if (!$dbError): ?>
            <p class="catalogue-count">
                <?php echo (int) $total; ?>
                vehicle<?php echo ($total === 1) ? '' : 's'; ?> found
            </p>

            <?php if (empty($vehicles)): ?>
                <!-- Empty state -->
                <div class="no-results">
                    <p>No vehicles found.</p>
                    <p><a href="catalogue.php">Clear the filters</a> to see everything.</p>
                </div>
            <?php else: ?>

                <!-- Responsive grid of vehicle cards -->
                <ul class="vehicle-grid">
                    <?php foreach ($vehicles as $v): ?>
                        <?php
                            $vehicleId = (int) $v['id'];
                            $imageSrc  = catalogue_image_src($v['image_path'] ?? null);
                            // A sensible alt text: the stored one, or "Year Make Model".
                            $altText   = trim((string) ($v['alt_text'] ?? '')) !== ''
                                ? $v['alt_text']
                                : $v['year'] . ' ' . $v['make'] . ' ' . $v['model'];
                        ?>
                        <li class="vehicle-card">
                            <a class="vehicle-card__link" href="vehicle.php?id=<?php echo $vehicleId; ?>">

                                <?php if ($imageSrc !== null): ?>
                                    <img class="vehicle-card__img"
                                         src="<?php echo e($imageSrc); ?>"
                                         alt="<?php echo e($altText); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <span class="vehicle-card__placeholder" aria-hidden="true">
                                        No image available
                                    </span>
                                <?php endif; ?>

                                <div class="vehicle-card__body">
                                    <h2 class="vehicle-card__title">
                                        <?php echo e($v['year'] . ' ' . $v['make'] . ' ' . $v['model']); ?>
                                    </h2>

                                    <p class="vehicle-card__price">
                                        <?php echo e(format_price($v['price'])); ?>
                                    </p>

                                    <ul class="vehicle-card__meta">
                                        <li><?php echo e(format_mileage($v['mileage'])); ?></li>
                                        <?php if (!empty($v['body_type'])): ?>
                                            <li><?php echo e($v['body_type']); ?></li>
                                        <?php endif; ?>
                                        <li><?php echo e($v['transmission']); ?></li>
                                        <li><?php echo e($v['fuel_type']); ?></li>
                                    </ul>

                                    <p class="vehicle-card__status">
                                        Status: <?php echo e(ucfirst($v['status'])); ?>
                                    </p>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Pagination: links keep the current filters and sort -->
                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Catalogue pages">
                        <?php if ($page > 1): ?>
                            <a class="pagination__link"
                               href="<?php echo e(catalogue_page_url($filters, $sortKey, $page - 1)); ?>">
                                &laquo; Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === $page): ?>
                                <span class="pagination__link pagination__link--current"
                                      aria-current="page"><?php echo $p; ?></span>
                            <?php else: ?>
                                <a class="pagination__link"
                                   href="<?php echo e(catalogue_page_url($filters, $sortKey, $p)); ?>">
                                    <?php echo $p; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a class="pagination__link"
                               href="<?php echo e(catalogue_page_url($filters, $sortKey, $page + 1)); ?>">
                                Next &raquo;
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

            <?php endif; ?>
        <?php endif; ?>

    </section>

<?php
require __DIR__ . '/includes/footer.php';
