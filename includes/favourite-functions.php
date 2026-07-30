<?php
/**
 * AutoVault - Small helpers shared by the favourites actions and pages.
 */

/**
 * Store one safe message in the session for the next request.
 */
function set_favourite_flash($message, $type = 'success') {
    $_SESSION['favourite_flash'] = [
        'message' => (string) $message,
        'type' => $type === 'error' ? 'error' : 'success',
    ];
}

/**
 * Read and remove the one-time favourites message.
 */
function take_favourite_flash() {
    $flash = $_SESSION['favourite_flash'] ?? null;
    unset($_SESSION['favourite_flash']);

    if (
        !is_array($flash)
        || !isset($flash['message'], $flash['type'])
        || !is_string($flash['message'])
        || !is_string($flash['type'])
    ) {
        return null;
    }

    return [
        'message' => $flash['message'],
        'type' => $flash['type'] === 'error' ? 'error' : 'success',
    ];
}

/**
 * Return only one of the application's fixed destinations.
 *
 * No submitted URL is ever used directly, so an attacker cannot turn this
 * into an external/open redirect.
 */
function favourite_return_url($returnTo, $vehicleId) {
    if ($returnTo === 'vehicle' && is_int($vehicleId) && $vehicleId > 0) {
        return 'vehicle.php?id=' . $vehicleId;
    }

    return 'favourites.php';
}
