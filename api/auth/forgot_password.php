<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 'Invalid request method.', false, 400);
}

$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response(null, 'Invalid CSRF token.', false, 403);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    json_response(null, 'Invalid JSON payload.', false, 400);
}

$email = validate_email($input['email'] ?? '', 'Email');
$recaptchaToken = $input['recaptchaToken'] ?? '';

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
    // ── Automatic Cleanup of Old/Used Tokens ──
    $pdo->query("DELETE FROM password_resets WHERE used = 1 OR expires_at < NOW() - INTERVAL 1 DAY");

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // ALWAYS return success to prevent user enumeration
    $successMessage = 'If that email is registered, a password reset link has been sent.';

    if ($user) {
        // Generate a secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token to DB
        $insertStmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $insertStmt->execute([$user['id'], $token, $expiresAt]);

        // Build the absolute URL for the reset link
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $resetLink = $protocol . "://" . $host . BASE_URL . "/pages/reset_password.php?token=" . $token;

        send_reset_email($email, $user['full_name'], $resetLink);
    }

    json_response(null, $successMessage, true, 200);

} catch (Exception $e) {
    json_error($e);
}