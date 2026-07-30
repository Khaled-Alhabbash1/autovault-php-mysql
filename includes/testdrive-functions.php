<?php
/**
 * AutoVault - Test-drive validation and one-time message helpers.
 */

function set_testdrive_flash($message, $type = 'success') {
    $_SESSION['testdrive_flash'] = [
        'message' => (string) $message,
        'type' => $type === 'error' ? 'error' : 'success',
    ];
}

function take_testdrive_flash() {
    $flash = $_SESSION['testdrive_flash'] ?? null;
    unset($_SESSION['testdrive_flash']);

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
 * Safely read a scalar form value. Arrays are rejected instead of converted.
 */
function testdrive_text_value(array $post, $key) {
    if (!isset($post[$key])) {
        return '';
    }

    return is_string($post[$key]) ? trim($post[$key]) : null;
}

/**
 * Validate the supported request fields.
 */
function validate_testdrive_form(array $post) {
    $values = [
        'preferred_date' => testdrive_text_value($post, 'preferred_date'),
        'preferred_time' => testdrive_text_value($post, 'preferred_time'),
        'phone' => testdrive_text_value($post, 'phone'),
        'message' => testdrive_text_value($post, 'message'),
    ];
    $errors = [];

    foreach ($values as $value) {
        if ($value === null) {
            $errors[] = 'Invalid form data was submitted.';
            return ['values' => [
                'preferred_date' => '',
                'preferred_time' => '',
                'phone' => '',
                'message' => '',
            ], 'errors' => $errors];
        }
    }

    if ($values['preferred_date'] === '') {
        $errors[] = 'Please choose a preferred date.';
    } else {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['preferred_date']);
        $dateErrors = DateTimeImmutable::getLastErrors();
        $dateIsValid = $date !== false
            && ($dateErrors === false || (
                $dateErrors['warning_count'] === 0
                && $dateErrors['error_count'] === 0
            ))
            && $date->format('Y-m-d') === $values['preferred_date'];

        if (!$dateIsValid) {
            $errors[] = 'Please choose a valid preferred date.';
        } elseif ($date < new DateTimeImmutable('today')) {
            $errors[] = 'The preferred date cannot be in the past.';
        }
    }

    if (
        $values['preferred_time'] !== ''
        && !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $values['preferred_time'])
    ) {
        $errors[] = 'Please choose a valid preferred time.';
    }

    if (
        $values['phone'] !== ''
        && (
            strlen($values['phone']) > 30
            || !preg_match('/^[0-9+().\-\s]{7,30}$/', $values['phone'])
        )
    ) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if (strlen($values['message']) > 2000) {
        $errors[] = 'The message must be 2,000 characters or fewer.';
    }

    return ['values' => $values, 'errors' => $errors];
}
