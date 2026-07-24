<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Logout
 * -------------------------------------------------------------------------
 * Safely logs the user out. This does NOT touch the database.
 *
 * For safety, logging out is only done through a POST request that carries a
 * valid CSRF token (the Logout button in the header sends one). This stops
 * another website from logging you out without your knowledge.
 * -------------------------------------------------------------------------
 */

require_once __DIR__ . '/includes/auth.php';

// Only log out on a valid POST with a matching CSRF token.
// Anything else simply returns the visitor to the home page.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify($_POST['csrf_token'] ?? '')) {

    // 1. Empty all the session data.
    $_SESSION = [];

    // 2. Remove the session cookie from the browser, if cookies are used.
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000, // a time in the past deletes the cookie
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // 3. Destroy the session on the server.
    session_destroy();
}

// 4. Send the visitor to the home page (Post/Redirect/Get).
header('Location: index.php');
exit;
