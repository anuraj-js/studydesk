<?php
require_once __DIR__ . '/../../includes/response.php';

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 'Invalid request method.', false, 400);
}

// Note: We intentionally DO NOT call require_api_auth() here.
// If an admin deletes a user mid-session, the user must still be able to log out.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

json_response(null, 'Logged out successfully.', true, 200);