<?php
// config/validator.php - Shared validation functions for all API endpoints
// Usage: require_once(__DIR__ . '/validator.php');

// Returns true if value is null, empty string, or only whitespace
function isEmpty(string $value): bool {
    return trim($value) === '';
}

// Returns true if value contains only letters, numbers, and underscores (min 3 chars)
function isAlphanumericUsername(string $value): bool {
    return preg_match('/^[a-zA-Z0-9_]{3,}$/', $value) === 1;
}

// Returns true if value is a valid email address
function isValidEmail(string $value): bool {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Returns true if value is exactly 10 digits
function isValidPhone(string $value): bool {
    return preg_match('/^\d{10}$/', $value) === 1;
}

// Returns true if password is min 8 chars, contains at least one letter and one number
function isValidPassword(string $value): bool {
    if (strlen($value) < 8) return false;
    if (!preg_match('/[a-zA-Z]/', $value)) return false;
    if (!preg_match('/[0-9]/', $value)) return false;
    return true;
}

// Returns true if value is a valid Y-m-d date
function isValidDate(string $value): bool {
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

// Returns true if the person is at least 13 years old based on DOB string (Y-m-d)
function isAtLeast13(string $dob): bool {
    if (!isValidDate($dob)) return false;
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    return $today->diff($dobDate)->y >= 13;
}

// Returns true if value contains at least one letter (rejects pure numbers/symbols)
function hasLetters(string $value): bool {
    return preg_match('/[a-zA-Z]/', $value) === 1;
}

// Returns true if value length is within the given max (inclusive)
function isWithinLength(string $value, int $max): bool {
    return strlen($value) <= $max;
}
?>