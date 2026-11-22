<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 'Invalid request method.', false, 400);
}

// Verify CSRF
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response(null, 'Invalid CSRF token.', false, 403);
}

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    json_response(null, 'Invalid JSON payload.', false, 400);
}

// Extract and validate inputs
$role = $input['role'] ?? '';
$identifier = validate_max_length($input['identifier'] ?? '', 150, 'Username or Email');
$password = $input['password'] ?? '';
$recaptchaToken = $input['recaptchaToken'] ?? '';

if (!$role || !in_array($role, ['user', 'admin'])) {
    json_response(null, 'Invalid role selected.', false, 400);
}

if (!$identifier || !$password) {
    json_response(null, 'Identifier and password are required.', false, 400);
}

// Verify reCAPTCHA
$env = parse_ini_file(__DIR__ . '/../../.env');
$secretKey = $env['RECAPTCHA_SECRET_KEY'] ?? '';

if ($secretKey) {
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $postData = http_build_query([
        'secret' => $secretKey,
        'response' => $recaptchaToken
    ]);
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($verifyUrl, false, $context);
    $responseKeys = json_decode($result, true);

    if (!$responseKeys || !$responseKeys['success']) {
        json_response(null, 'CAPTCHA verification failed. Please try again.', false, 400);
    }
}

try {
    // ── Automatic Cleanup of Old Login Attempts ──
    $pdo->query("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY");

    // ── Rate Limiting ──
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE identifier = ? AND attempted_at > NOW() - INTERVAL 10 MINUTE");
    $stmt->execute([$identifier]);
    $failedAttempts = (int)$stmt->fetchColumn();

    if ($failedAttempts >= 5) {
        json_response(null, 'Too many failed login attempts. Please try again after 10 minutes.', false, 429);
    }

    // ── Authenticate ──
    $table = ($role === 'user') ? 'users' : 'admins';
    
    // Admins don't have an is_active column, so we build the query conditionally
    $selectCols = ($role === 'user') ? "id, username, password, is_active" : "id, username, password";
    
    $stmt = $pdo->prepare("SELECT {$selectCols} FROM {$table} WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $account = $stmt->fetch();

    if (!$account || !password_verify($password, $account['password'])) {
        // Log failed attempt
        $logStmt = $pdo->prepare("INSERT INTO login_attempts (identifier) VALUES (?)");
        $logStmt->execute([$identifier]);
        
        json_response(null, 'Invalid username/email or password.', false, 400);
    }

    // Check active status for users BEFORE clearing rate limits
    if ($role === 'user' && isset($account['is_active']) && $account['is_active'] == 0) {
        json_response(null, 'Your account has been deactivated.', false, 403);
    }

    // ── Success ──
    // Clear failed attempts ONLY after all checks pass
    $clearStmt = $pdo->prepare("DELETE FROM login_attempts WHERE identifier = ?");
    $clearStmt->execute([$identifier]);

    // Setup session securely
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $account['id'];
    $_SESSION['role'] = $role;
    $_SESSION['username'] = $account['username']; 
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // regenerate CSRF on login

    // Use the dynamic BASE_URL constant
    $redirect = ($role === 'admin') ? BASE_URL . '/pages/admin/home.php' : BASE_URL . '/pages/home.php';

    json_response(['redirect' => $redirect], 'Login successful.', true, 200);

} catch (Exception $e) {
    json_error($e);
}