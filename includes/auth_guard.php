<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensures the current session is authenticated and valid for an API endpoint.
 * Exits with a 401 JSON response if not authenticated.
 *
 * @return void
 */
function require_api_auth(): void {
    global $pdo;

    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        json_response(null, 'Unauthenticated.', false, 401);
    }

    // Verify the account still exists and is active
    if ($_SESSION['role'] === 'user') {
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || $user['is_active'] == 0) {
            session_destroy();
            json_response(null, 'Your account has been deactivated or deleted.', false, 401);
        }
    } elseif ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            session_destroy();
            json_response(null, 'Admin account not found.', false, 401);
        }
    }
}

/**
 * Ensures the current session has the correct role for an API endpoint.
 * Exits with a 403 JSON response if the role is incorrect.
 *
 * @param string $expectedRole
 * @return void
 */
function require_api_role(string $expectedRole): void {
    require_api_auth();
    
    if (empty($_SESSION['role']) || $_SESSION['role'] !== $expectedRole) {
        json_response(null, 'Forbidden.', false, 403);
    }
}

/**
 * Ensures the current session is authenticated and valid for a protected HTML page.
 * Redirects to the login page if not authenticated or inactive.
 *
 * @return void
 */
function require_page_auth(): void {
    global $pdo;

    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }

    // Verify the account still exists and is active
    if ($_SESSION['role'] === 'user') {
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || $user['is_active'] == 0) {
            session_destroy();
            header('Location: ' . BASE_URL . '/pages/login.php');
            exit;
        }
    } elseif ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            session_destroy();
            header('Location: ' . BASE_URL . '/pages/login.php');
            exit;
        }
    }
}

/**
 * Ensures the current session has the correct role for a protected HTML page.
 * Redirects to the dashboard if the role is incorrect, or to login if no role exists.
 *
 * @param string $expectedRole
 * @return void
 */
function require_page_role(string $expectedRole): void {
    require_page_auth();
    
    if ($_SESSION['role'] !== $expectedRole) {
        // Role mismatch, do not destroy session, just redirect to their appropriate dashboard
        $redirect = ($_SESSION['role'] === 'admin') ? BASE_URL . '/pages/admin/home.php' : BASE_URL . '/pages/home.php';
        header('Location: ' . $redirect);
        exit;
    }
}

// ── Explicit Wrapper Functions for Readability ──

function require_user_api(): void { require_api_role('user'); }
function require_admin_api(): void { require_api_role('admin'); }
function require_user_page(): void { require_page_role('user'); }
function require_admin_page(): void { require_page_role('admin'); }