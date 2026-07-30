<?php
/**
 * -------------------------------------------------------------------------
 * AutoVault - Authentication & session helper
 * -------------------------------------------------------------------------
 * This is the ONE reusable place that:
 *   - starts the session safely (with secure cookie settings)
 *   - creates and checks CSRF tokens (to protect forms)
 *   - tells pages whether someone is logged in and who they are
 *   - provides a small escaping helper for safe output
 *
 * Any page that needs login state or CSRF protection includes this file:
 *     require_once __DIR__ . '/includes/auth.php';
 *
 * It is safe to include more than once (require_once + guards below).
 * -------------------------------------------------------------------------
 */

// -------------------------------------------------------------------------
// 1. Start the session safely (only if one is not already started).
// -------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {

    // Secure cookie settings where the environment supports them.
    $cookieParams = [
        'lifetime' => 0,      // session cookie: cleared when the browser closes
        'path'     => '/',    // available across the whole site
        'httponly' => true,   // JavaScript cannot read the cookie (helps stop theft)
        'samesite' => 'Lax',  // basic protection against cross-site requests
    ];

    // Only mark the cookie "secure" on HTTPS, so local http testing still works.
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $cookieParams['secure'] = true;
    }

    session_set_cookie_params($cookieParams);
    session_start();
}

// -------------------------------------------------------------------------
// 2. Small helper to safely escape text before printing it in HTML.
//    Using this everywhere helps prevent XSS (cross-site scripting).
// -------------------------------------------------------------------------
function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// -------------------------------------------------------------------------
// 3. CSRF protection helpers.
//    A CSRF token is a secret random value stored in the session and also
//    placed inside each form. When the form is submitted we check that the
//    two match, which proves the request came from our own page.
// -------------------------------------------------------------------------

// Return the current CSRF token, creating one the first time it is needed.
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Return a ready-to-use hidden form field containing the CSRF token.
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Safely check a submitted token against the one in the session.
// hash_equals() compares in a way that does not leak timing information.
function csrf_verify($token) {
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

// -------------------------------------------------------------------------
// 4. Login-state helpers.
// -------------------------------------------------------------------------

// Is someone logged in right now?
function is_logged_in() {
    return isset($_SESSION['user']['id']);
}

// Return the logged-in user's session data, or null if logged out.
function current_user() {
    return is_logged_in() ? $_SESSION['user'] : null;
}

// Is the authenticated account an administrator?
function is_admin() {
    return is_logged_in()
        && isset($_SESSION['user']['role'])
        && $_SESSION['user']['role'] === 'admin';
}

/**
 * Shared administrator gate.
 *
 * Logged-out visitors are redirected to login. Logged-in non-administrators
 * receive a 403 response and the caller can render a friendly denial page.
 */
function require_admin($loginPath = 'login.php') {
    if (!is_logged_in()) {
        header('Location: ' . $loginPath);
        exit;
    }

    if (!is_admin()) {
        http_response_code(403);
        return false;
    }

    return true;
}
