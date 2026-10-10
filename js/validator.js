// js/validator.js - Client-side validation functions
// Mirror of config/validator.php — same rules on both sides.
// Load this before any form JS file that needs validation.
// Usage: <script src="../js/validator.js"></script>

const Validator = (() => {

    // Returns true if value is null, empty string, or only whitespace
    function isEmpty(value) {
        return !value || value.trim() === '';
    }

    // Returns true if value starts with a digit
    function startsWithNumber(value) {
        return /^\d/.test(value);
    }

    // Returns true if value is a valid username (letters, numbers, underscores; 3–50 chars, cannot start with a digit)
    function isAlphanumericUsername(value) {
        if (!/^[a-zA-Z0-9_]{3,50}$/.test(value)) return false;
        if (startsWithNumber(value)) return false;
        return true;
    }

    // Returns true if value looks like a valid email address
    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@.]+(\.[^\s@.]+)+$/.test(value);
    }

    // Returns true if value is exactly 10 digits
    function isValidPhone(value) {
        return /^\d{10}$/.test(value);
    }

    // Returns true if password is 8–72 chars, contains at least one letter and one number
    function isValidPassword(value) {
        if (!value) return false;
        if (value.length < 8 || value.length > 72) return false;
        if (!/[a-zA-Z]/.test(value)) return false;
        if (!/[0-9]/.test(value)) return false;
        return true;
    }

    // Returns true if value is a valid YYYY-MM-DD date
    function isValidDate(value) {
        if (!value) return false;
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
        const date = new Date(value);
        if (isNaN(date.getTime())) return false;
        const [y, m, d] = value.split('-').map(Number);
        return date.getFullYear() === y &&
               date.getMonth() + 1 === m &&
               date.getDate() === d;
    }

    // Returns true if person is at least 13 years old based on DOB string (YYYY-MM-DD)
    function isAtLeast13(dob) {
        if (!isValidDate(dob)) return false;
        const dobDate = new Date(dob);
        const today = new Date();
        const cutoff = new Date(dobDate.getFullYear() + 13, dobDate.getMonth(), dobDate.getDate());
        return today >= cutoff;
    }

    // Returns true if value contains at least one letter (rejects pure numbers/symbols)
    function hasLetters(value) {
        return /[a-zA-Z]/.test(value);
    }

    // Returns true if value length is within the given max (inclusive)
    function isWithinLength(value, max) {
        return value.length <= max;
    }

    return {
        isEmpty,
        startsWithNumber,
        isAlphanumericUsername,
        isValidEmail,
        isValidPhone,
        isValidPassword,
        isValidDate,
        isAtLeast13,
        hasLetters,
        isWithinLength
    };

})();