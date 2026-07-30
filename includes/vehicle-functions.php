<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Vehicle helper functions
 * -------------------------------------------------------------------------
 * Small, reusable helpers for the vehicle catalogue:
 *   - fixed whitelists for sorting and the enum filters
 *   - a single place that turns the GET filters into a safe SQL WHERE clause
 *     (so the COUNT query and the main query never duplicate that logic)
 *   - formatting helpers for price and mileage
 *   - safe handling of image paths coming from the database
 *
 * None of these functions touch the database directly; catalogue.php runs
 * the prepared statements using what these helpers return.
 * -------------------------------------------------------------------------
 */

/**
 * The ONLY sort options the catalogue accepts.
 * Each key maps to a FIXED "ORDER BY" clause, so the raw sort value from the
 * URL is never placed into SQL - we only ever use these trusted strings.
 */
function catalogue_sort_options() {
    return [
        'newest'      => ['label' => 'Newest year first',   'sql' => 'v.year DESC, v.id DESC'],
        'oldest'      => ['label' => 'Oldest year first',   'sql' => 'v.year ASC, v.id ASC'],
        'price_asc'   => ['label' => 'Price: low to high',  'sql' => 'v.price ASC, v.id DESC'],
        'price_desc'  => ['label' => 'Price: high to low',  'sql' => 'v.price DESC, v.id DESC'],
        'mileage_asc' => ['label' => 'Mileage: low to high','sql' => '(v.mileage IS NULL), v.mileage ASC, v.id DESC'],
        'make_az'     => ['label' => 'Make: A to Z',        'sql' => 'v.make ASC, v.model ASC'],
    ];
}

// The transmission values allowed by the schema (ENUM).
function catalogue_transmissions() {
    return ['Automatic', 'Manual'];
}

// The fuel-type values allowed by the schema (ENUM).
function catalogue_fuel_types() {
    return ['Petrol', 'Diesel', 'Electric', 'Hybrid', 'Other'];
}

/**
 * Validate a vehicle ID from GET or POST.
 *
 * The vehicles.id column is an UNSIGNED INT, so valid IDs are positive
 * base-10 integers no larger than 4,294,967,295. Returning null gives every
 * page one consistent way to reject missing, array, decimal and invalid IDs.
 */
function parse_vehicle_id($value) {
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

/**
 * Turn the raw GET values into:
 *   - 'where'   : an array of SQL conditions using NAMED placeholders
 *   - 'params'  : the matching values for those placeholders
 *   - 'filters' : the cleaned values to redisplay in the form
 *
 * Every value is validated here. Invalid numbers are ignored (not placed in
 * the SQL) but are still returned in 'filters' so the form can show what the
 * visitor typed. Only whitelisted columns are ever filtered on.
 */
function build_catalogue_filters(array $get) {

    // Always limit the public catalogue to vehicles that are available.
    $where  = ['v.status = :status'];
    $params = [':status' => 'available'];

    // Cleaned values for redisplay (start empty).
    $filters = [
        'q'            => '',
        'make'         => '',
        'body_type'    => '',
        'transmission' => '',
        'fuel_type'    => '',
        'min_year'     => '',
        'max_year'     => '',
        'min_price'    => '',
        'max_price'    => '',
    ];

    // ---- Keyword: searches make and model ----
    $q = trim((string) ($get['q'] ?? ''));
    $filters['q'] = $q;
    if ($q !== '') {
        // LIKE with bound values - still a prepared statement, no concatenation.
        // NOTE: MySQL native prepared statements (emulation off) do not allow
        // the SAME named placeholder to appear twice, so we use one for make
        // and one for model, both set to the same search value.
        $where[] = '(v.make LIKE :kw_make OR v.model LIKE :kw_model)';
        $params[':kw_make']  = '%' . $q . '%';
        $params[':kw_model'] = '%' . $q . '%';
    }

    // ---- Make: exact match ----
    $make = trim((string) ($get['make'] ?? ''));
    $filters['make'] = $make;
    if ($make !== '') {
        $where[] = 'v.make = :make';
        $params[':make'] = $make;
    }

    // ---- Body type: exact match ----
    $bodyType = trim((string) ($get['body_type'] ?? ''));
    $filters['body_type'] = $bodyType;
    if ($bodyType !== '') {
        $where[] = 'v.body_type = :body_type';
        $params[':body_type'] = $bodyType;
    }

    // ---- Transmission: must be one of the allowed values ----
    $transmission = trim((string) ($get['transmission'] ?? ''));
    $filters['transmission'] = $transmission;
    if ($transmission !== '' && in_array($transmission, catalogue_transmissions(), true)) {
        $where[] = 'v.transmission = :transmission';
        $params[':transmission'] = $transmission;
    }

    // ---- Fuel type: must be one of the allowed values ----
    $fuel = trim((string) ($get['fuel_type'] ?? ''));
    $filters['fuel_type'] = $fuel;
    if ($fuel !== '' && in_array($fuel, catalogue_fuel_types(), true)) {
        $where[] = 'v.fuel_type = :fuel_type';
        $params[':fuel_type'] = $fuel;
    }

    // ---- Year range: whole numbers only ----
    $minYear = trim((string) ($get['min_year'] ?? ''));
    $filters['min_year'] = $minYear;
    if (preg_match('/^\d{1,4}$/', $minYear)) {
        $where[] = 'v.year >= :min_year';
        $params[':min_year'] = (int) $minYear;
    }

    $maxYear = trim((string) ($get['max_year'] ?? ''));
    $filters['max_year'] = $maxYear;
    if (preg_match('/^\d{1,4}$/', $maxYear)) {
        $where[] = 'v.year <= :max_year';
        $params[':max_year'] = (int) $maxYear;
    }

    // ---- Price range: numbers, optionally with up to two decimals ----
    $minPrice = trim((string) ($get['min_price'] ?? ''));
    $filters['min_price'] = $minPrice;
    if (preg_match('/^\d+(\.\d{1,2})?$/', $minPrice)) {
        $where[] = 'v.price >= :min_price';
        $params[':min_price'] = (float) $minPrice;
    }

    $maxPrice = trim((string) ($get['max_price'] ?? ''));
    $filters['max_price'] = $maxPrice;
    if (preg_match('/^\d+(\.\d{1,2})?$/', $maxPrice)) {
        $where[] = 'v.price <= :max_price';
        $params[':max_price'] = (float) $maxPrice;
    }

    return ['where' => $where, 'params' => $params, 'filters' => $filters];
}

/**
 * Format a price for display, e.g. 12500.00 -> "$12,500".
 */
function format_price($price) {
    return '$' . number_format((float) $price, 0);
}

/**
 * Format mileage for display, e.g. 45000 -> "45,000 km".
 * Returns a friendly fallback when mileage is not recorded.
 */
function format_mileage($mileage) {
    if ($mileage === null || $mileage === '') {
        return 'Mileage not listed';
    }
    return number_format((int) $mileage) . ' km';
}

/**
 * Turn a database image path into a value that is safe to use in an <img src>.
 * Returns null when the path is missing or unsafe, so the caller can show a
 * placeholder instead.
 *
 * Only repository-owned images below assets/images/vehicles/ are allowed.
 * This blocks directory traversal, external tracking URLs, protocol-relative
 * URLs, JavaScript/data/file schemes, and unrelated local application files.
 */
function catalogue_image_src($path) {
    $path = trim((string) ($path ?? ''));

    if ($path === '') {
        return null;
    }

    // Decode once as well so encoded traversal such as %2e%2e is rejected.
    $decodedPath = rawurldecode($path);
    if (
        strpos($decodedPath, '..') !== false
        || strpos($decodedPath, '\\') !== false
        || strpos($decodedPath, '//') !== false
    ) {
        return null;
    }

    // Keep paths inside the one public vehicle-image directory.
    if (!preg_match('#^assets/images/vehicles/[A-Za-z0-9][A-Za-z0-9._/-]*$#D', $path)) {
        return null;
    }

    return $path;
}
