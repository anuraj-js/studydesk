<?php
require_once __DIR__ . '/response.php';

/**
 * Ensures a value is present and not an empty string after trimming.
 */
function validate_required(mixed $value, string $fieldName): string {
    if (!isset($value) || trim((string)$value) === '') {
        json_response(null, "The {$fieldName} field is required.", false, 400);
    }
    return trim((string)$value);
}

/**
 * Ensures a value is a valid email address.
 */
function validate_email(mixed $value, string $fieldName): string {
    $email = validate_required($value, $fieldName);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(null, "Please provide a valid email address for {$fieldName}.", false, 400);
    }
    return $email;
}

/**
 * Ensures a string meets a minimum length requirement.
 */
function validate_min_length(mixed $value, int $min, string $fieldName): string {
    $str = validate_required($value, $fieldName);
    if (mb_strlen($str) < $min) {
        json_response(null, "The {$fieldName} must be at least {$min} characters long.", false, 400);
    }
    return $str;
}

/**
 * Ensures a string does not exceed a maximum length requirement.
 */
function validate_max_length(mixed $value, int $max, string $fieldName): string {
    $str = trim((string)$value);
    if (mb_strlen($str) > $max) {
        json_response(null, "The {$fieldName} must not exceed {$max} characters.", false, 400);
    }
    return $str;
}

/**
 * Ensures a value matches the YYYY-MM-DD date format and is a real date.
 */
function validate_date(mixed $value, string $fieldName, bool $required = true): ?string {
    if (!$required && (empty($value) || trim((string)$value) === '')) {
        return null;
    }
    
    $dateStr = validate_required($value, $fieldName);
    $d = DateTime::createFromFormat('Y-m-d', $dateStr);
    
    if (!$d || $d->format('Y-m-d') !== $dateStr) {
        json_response(null, "The {$fieldName} must be a valid date in YYYY-MM-DD format.", false, 400);
    }
    return $dateStr;
}

/**
 * Ensures a value matches the HH:MM time format (or HH:MM:SS).
 */
function validate_time(mixed $value, string $fieldName, bool $required = true): ?string {
    if (!$required && (empty($value) || trim((string)$value) === '')) {
        return null;
    }
    
    $timeStr = validate_required($value, $fieldName);
    
    $d = DateTime::createFromFormat('H:i', $timeStr);
    if (!$d || $d->format('H:i') !== $timeStr) {
        $d = DateTime::createFromFormat('H:i:s', $timeStr);
        if (!$d || $d->format('H:i:s') !== $timeStr) {
            json_response(null, "The {$fieldName} must be a valid time in HH:MM format.", false, 400);
        }
    }
    return $timeStr;
}

/**
 * Ensures a value is one of the allowed ENUM options.
 */
function validate_enum(mixed $value, array $allowed, string $fieldName): string {
    $str = validate_required($value, $fieldName);
    if (!in_array($str, $allowed, true)) {
        json_response(null, "Invalid value for {$fieldName}.", false, 400);
    }
    return $str;
}

/**
 * Ensures a valid date is today or in the future.
 */
function validate_future_or_today(?string $date, string $fieldName): ?string {
    if (!$date) return null;
    
    $today = gmdate('Y-m-d');
    if ($date < $today) {
        json_response(null, "The {$fieldName} cannot be in the past.", false, 400);
    }
    return $date;
}

/**
 * Ensures a combined Date and Time is in the future.
 */
function validate_future_datetime(?string $date, ?string $time, string $fieldName): void {
    if (!$date || !$time) return;
    
    // Combine into standard format
    $datetimeStr = $date . ' ' . $time;
    
    // Parse in UTC
    $selected = new DateTime($datetimeStr, new DateTimeZone('UTC'));
    $now = new DateTime('now', new DateTimeZone('UTC'));
    
    // If selected is in the past, reject it. (Leaving a 1-minute buffer helps prevent instant expiration)
    if ($selected < $now) {
        json_response(null, "The {$fieldName} cannot be in the past.", false, 400);
    }
}