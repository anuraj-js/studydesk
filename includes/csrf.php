<?php
// ── Security Headers ──
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; ";
    $csp .= "img-src 'self' data: https://www.gstatic.com; ";
    $csp .= "font-src 'self' https://cdnjs.cloudflare.com; ";
    $csp .= "frame-src https://www.google.com; ";
    $csp .= "connect-src 'self';";
    
    header("Content-Security-Policy: " . $csp);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generates (or retrieves) the CSRF token for the current session.
 */
function csrf_generate(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies the provided CSRF token against the session token.
 */
function csrf_verify(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}