<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../config/db.php';

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

$token = validate_max_length($input['token'] ?? '', 64, 'Token');
$newPassword = $input['new_password'] ?? '';

if (!$token || !$newPassword) {
    json_response(null, 'Token and new password are required.', false, 400);
}

// Validate Password Complexity
$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,64}$/';
if (!preg_match($passwordRegex, $newPassword)) {
    json_response(null, 'Password must be 8-64 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.', false, 400);
}

try {
    // Verify token
    $stmt = $pdo->prepare("
        SELECT user_id 
        FROM password_resets 
        WHERE token = ? AND used = 0 AND expires_at > NOW() 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();

    if (!$resetRequest) {
        json_response(null, 'This reset link is invalid or has expired.', false, 400);
    }

    $userId = $resetRequest['user_id'];

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update user password
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $userId]);

    // Mark token as used
    $markUsedStmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $markUsedStmt->execute([$token]);

    json_response(null, 'Password updated successfully.', true, 200);

} catch (Exception $e) {
    json_error($e);
}