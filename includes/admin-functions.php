<?php
/**
 * AutoVault - Reusable administrator authorization and vehicle form helpers.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/vehicle-functions.php';

/**
 * Protect an admin page and render a safe denial for normal users.
 */
function require_admin_page() {
    if (require_admin('../login.php')) {
        return;
    }

    $baseHref = '../';
    $pageTitle = 'Access denied';
    $metaDescription = 'Administrator access is required.';
    require __DIR__ . '/header.php';
    ?>
        <section class="vehicle-message" role="alert">
            <h1>Access denied</h1>
            <p>You do not have permission to view this page.</p>
            <a class="button" href="index.php">Return home</a>
        </section>
    <?php
    require __DIR__ . '/footer.php';
    exit;
}

/**
 * Validate any schema UNSIGNED INT identifier used by admin pages.
 */
function parse_admin_id($value) {
    if (
        !is_string($value)
        || !preg_match('/^[1-9][0-9]*$/', $value)
        || strlen($value) > 10
        || (int) $value > 4294967295
    ) {
        return null;
    }

    return (int) $value;
}

function admin_user_roles() {
    return ['user', 'admin'];
}

function admin_request_statuses() {
    return ['pending', 'confirmed', 'completed', 'cancelled'];
}

/**
 * Return a strict YYYY-MM-DD date or an empty value.
 */
function admin_filter_date($value) {
    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (
        $date === false
        || ($dateErrors !== false && (
            $dateErrors['warning_count'] > 0
            || $dateErrors['error_count'] > 0
        ))
        || $date->format('Y-m-d') !== $value
    ) {
        return '';
    }

    return $value;
}

/**
 * Build a pagination URL from already validated filters.
 */
function admin_page_url($path, array $filters, $page) {
    $query = [];
    foreach ($filters as $key => $value) {
        if ($value !== '') {
            $query[$key] = $value;
        }
    }
    $query['page'] = max(1, (int) $page);

    return $path . '?' . http_build_query($query);
}

function admin_vehicle_statuses() {
    return ['available', 'reserved', 'sold'];
}

function admin_vehicle_defaults() {
    return [
        'make' => '',
        'model' => '',
        'year' => '',
        'price' => '',
        'mileage' => '',
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'body_type' => '',
        'color' => '',
        'doors' => '',
        'seats' => '',
        'vin' => '',
        'description' => '',
        'status' => 'available',
        'is_featured' => '0',
    ];
}

function admin_form_text(array $post, $key) {
    if (!isset($post[$key])) {
        return '';
    }

    return is_string($post[$key]) ? trim($post[$key]) : null;
}

/**
 * Validate only the fixed vehicle fields supported by schema.sql.
 */
function validate_admin_vehicle_form(array $post) {
    $values = admin_vehicle_defaults();
    $errors = [];

    foreach (array_keys($values) as $key) {
        if ($key === 'is_featured') {
            $rawFeatured = $post[$key] ?? '0';
            if (!is_string($rawFeatured)) {
                $errors[] = 'Invalid form data was submitted.';
                return ['values' => $values, 'errors' => $errors];
            }
            $values[$key] = is_string($rawFeatured) && $rawFeatured === '1' ? '1' : '0';
            continue;
        }

        $value = admin_form_text($post, $key);
        if ($value === null) {
            $errors[] = 'Invalid form data was submitted.';
            return ['values' => $values, 'errors' => $errors];
        }
        $values[$key] = $value;
    }

    if ($values['make'] === '' || strlen($values['make']) > 50) {
        $errors[] = 'Make is required and must be 50 characters or fewer.';
    }
    if ($values['model'] === '' || strlen($values['model']) > 50) {
        $errors[] = 'Model is required and must be 50 characters or fewer.';
    }

    $maximumYear = (int) date('Y') + 1;
    if (
        !preg_match('/^[0-9]{4}$/', $values['year'])
        || (int) $values['year'] < 1886
        || (int) $values['year'] > $maximumYear
    ) {
        $errors[] = 'Year must be between 1886 and ' . $maximumYear . '.';
    }

    if (
        !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/', $values['price'])
        || (float) $values['price'] > 99999999.99
    ) {
        $errors[] = 'Price must be a non-negative amount with up to two decimals.';
    }

    if (
        $values['mileage'] !== ''
        && (
            !preg_match('/^[0-9]+$/', $values['mileage'])
            || (float) $values['mileage'] > 4294967295
        )
    ) {
        $errors[] = 'Mileage must be a non-negative whole number.';
    }

    if (!in_array($values['fuel_type'], catalogue_fuel_types(), true)) {
        $errors[] = 'Please choose a valid fuel type.';
    }
    if (!in_array($values['transmission'], catalogue_transmissions(), true)) {
        $errors[] = 'Please choose a valid transmission.';
    }
    if (!in_array($values['status'], admin_vehicle_statuses(), true)) {
        $errors[] = 'Please choose a valid status.';
    }

    foreach (['body_type' => 50, 'color' => 30, 'vin' => 50] as $key => $limit) {
        if (strlen($values[$key]) > $limit) {
            $errors[] = ucfirst(str_replace('_', ' ', $key)) . " must be $limit characters or fewer.";
        }
    }

    foreach (['doors', 'seats'] as $key) {
        if (
            $values[$key] !== ''
            && (
                !preg_match('/^[0-9]+$/', $values[$key])
                || (int) $values[$key] < 1
                || (int) $values[$key] > 255
            )
        ) {
            $errors[] = ucfirst($key) . ' must be between 1 and 255.';
        }
    }

    if (strlen($values['description']) > 10000) {
        $errors[] = 'Description must be 10,000 characters or fewer.';
    }

    return ['values' => $values, 'errors' => $errors];
}

function set_admin_flash($message, $type = 'success') {
    $_SESSION['admin_flash'] = [
        'message' => (string) $message,
        'type' => $type === 'error' ? 'error' : 'success',
    ];
}

function take_admin_flash() {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    if (!is_array($flash) || !isset($flash['message'], $flash['type'])) {
        return null;
    }

    return [
        'message' => (string) $flash['message'],
        'type' => $flash['type'] === 'error' ? 'error' : 'success',
    ];
}

/**
 * Shared create/edit form. The caller supplies only a fixed internal action.
 */
function render_admin_vehicle_form(array $values, array $errors, $action, $submitLabel) {
    ?>
    <?php if ($errors): ?>
        <div class="form-errors" id="admin-vehicle-errors" role="alert">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="admin-form" action="<?php echo e($action); ?>" method="post" novalidate
          <?php echo $errors ? 'aria-describedby="admin-vehicle-errors"' : ''; ?>>
        <?php echo csrf_field(); ?>
        <div class="admin-form__grid">
            <div class="form-group">
                <label for="make">Make</label>
                <input id="make" name="make" maxlength="50" required
                       value="<?php echo e($values['make']); ?>">
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input id="model" name="model" maxlength="50" required
                       value="<?php echo e($values['model']); ?>">
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <select id="year" name="year" required>
                    <option value="">Select a year</option>
                    <?php foreach (admin_year_choices($values['year']) as $yearOption): ?>
                        <option value="<?php echo (int) $yearOption; ?>"
                            <?php echo ($values['year'] === (string) $yearOption) ? 'selected' : ''; ?>>
                            <?php echo (int) $yearOption; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required
                       value="<?php echo e($values['price']); ?>">
            </div>
            <div class="form-group">
                <label for="mileage">Mileage (km)</label>
                <input type="number" id="mileage" name="mileage" min="0"
                       value="<?php echo e($values['mileage']); ?>">
            </div>
            <div class="form-group">
                <label for="body_type">Body type</label>
                <input id="body_type" name="body_type" maxlength="50"
                       value="<?php echo e($values['body_type']); ?>">
            </div>
            <div class="form-group">
                <label for="fuel_type">Fuel type</label>
                <select id="fuel_type" name="fuel_type">
                    <?php foreach (catalogue_fuel_types() as $option): ?>
                        <option value="<?php echo e($option); ?>"
                            <?php echo $values['fuel_type'] === $option ? 'selected' : ''; ?>>
                            <?php echo e($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="transmission">Transmission</label>
                <select id="transmission" name="transmission">
                    <?php foreach (catalogue_transmissions() as $option): ?>
                        <option value="<?php echo e($option); ?>"
                            <?php echo $values['transmission'] === $option ? 'selected' : ''; ?>>
                            <?php echo e($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="color">Colour</label>
                <input id="color" name="color" maxlength="30"
                       value="<?php echo e($values['color']); ?>">
            </div>
            <div class="form-group">
                <label for="doors">Doors</label>
                <input type="number" id="doors" name="doors" min="1" max="255"
                       value="<?php echo e($values['doors']); ?>">
            </div>
            <div class="form-group">
                <label for="seats">Seats</label>
                <input type="number" id="seats" name="seats" min="1" max="255"
                       value="<?php echo e($values['seats']); ?>">
            </div>
            <div class="form-group">
                <label for="vin">VIN</label>
                <input id="vin" name="vin" maxlength="50"
                       value="<?php echo e($values['vin']); ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach (admin_vehicle_statuses() as $option): ?>
                        <option value="<?php echo e($option); ?>"
                            <?php echo $values['status'] === $option ? 'selected' : ''; ?>>
                            <?php echo e(ucfirst($option)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group admin-form__checkbox">
                <label>
                    <input type="checkbox" name="is_featured" value="1"
                        <?php echo $values['is_featured'] === '1' ? 'checked' : ''; ?>>
                    Featured vehicle
                </label>
            </div>
            <div class="form-group admin-form__wide">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="7"
                          maxlength="10000"><?php echo e($values['description']); ?></textarea>
            </div>
        </div>
        <div class="admin-actions">
            <button class="button" type="submit"><?php echo e($submitLabel); ?></button>
            <a class="button button-secondary" href="admin/vehicles.php">Cancel</a>
        </div>
    </form>
    <?php
}
