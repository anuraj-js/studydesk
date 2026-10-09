<?php
// config/headers.php - Security headers for all API responses
// Usage: require_once(__DIR__ . '/headers.php');
// Include at the top of every API file, before any output.

// Prevent browsers from MIME-sniffing the response type
header('X-Content-Type-Options: nosniff');

// Block this app from being embedded in iframes (clickjacking protection)
header('X-Frame-Options: DENY');
?>