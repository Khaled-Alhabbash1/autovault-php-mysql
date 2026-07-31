<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - EXAMPLE database configuration
 * -------------------------------------------------------------------------
 * This is a TEMPLATE. It is safe to commit to Git because it contains no
 * real passwords.
 *
 * HOW TO USE (do this once when setting up the project):
 *   1. Copy this file and name the copy  config.php  (same "includes" folder).
 *   2. Open config.php and enter your REAL database details.
 *   3. config.php is listed in .gitignore, so your real password is never
 *      committed to Git.
 *
 * NEVER put a real password in this example file.
 * -------------------------------------------------------------------------
 */

// Only define these settings if they have not already been defined,
// so including the file twice does not cause an error.
if (!defined('DB_HOST')) {

    define('DB_HOST', '127.0.0.1');  // MySQL server host (usually 127.0.0.1)
    define('DB_PORT', '3306');       // MySQL server port (default is 3306)
    define('DB_NAME', 'autovault');  // Name of the database to use
    define('DB_USER', 'root');       // Database username
    define('DB_PASS', '');           // Database password - set the REAL one in config.php
    define('DB_CHARSET', 'utf8mb4'); // Character set (utf8mb4 supports emojis, etc.)
}

// -------------------------------------------------------------------------
// PRODUCTION ONLY (live server such as myweb.cs.uwindsor.ca):
// Hide PHP errors from visitors - they are still written to the server log.
// In your REAL includes/config.php on the live server, remove the "// " from
// the next two lines. Leave them commented on your local machine so you can
// still see errors while developing.
// -------------------------------------------------------------------------
// error_reporting(E_ALL);
// ini_set('display_errors', '0');
