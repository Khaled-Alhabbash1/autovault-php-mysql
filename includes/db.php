<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Database connection (PDO)
 * -------------------------------------------------------------------------
 * This file creates ONE shared database connection called $pdo that other
 * pages can use. It uses PDO (PHP Data Objects) so that later milestones can
 * run safe, prepared SQL statements.
 *
 * Other pages use it like this:
 *     require_once __DIR__ . '/includes/db.php';
 *     $stmt = $pdo->prepare('SELECT * FROM vehicles WHERE id = ?');
 *     $stmt->execute([$id]);
 * -------------------------------------------------------------------------
 */

// 1. Load the configuration.
//    Prefer the real config.php (which holds your real password and is
//    ignored by Git). If it does not exist yet, fall back to the example.
$localConfig   = __DIR__ . '/config.php';
$exampleConfig = __DIR__ . '/config.example.php';

if (file_exists($localConfig)) {
    require_once $localConfig;
} else {
    require_once $exampleConfig;
}

// 2. Build the DSN (Data Source Name). This string tells PDO where the
//    database is and which character set to use.
$dsn = 'mysql:host=' . DB_HOST
     . ';port=' . DB_PORT
     . ';dbname=' . DB_NAME
     . ';charset=' . DB_CHARSET;

// 3. PDO options that make the connection safer and easier to work with.
$options = [
    // Throw an exception when a query fails (instead of failing silently).
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Return rows as associative arrays, e.g. $row['make'].
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Use real prepared statements from the database, not emulated ones.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 4. Try to connect. $pdo is the connection other pages will reuse.
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Never show the raw error (it can leak details). Show a friendly
    // message and stop the page.
    http_response_code(500);
    exit('Database connection failed. Please check your settings in includes/config.php.');
}
