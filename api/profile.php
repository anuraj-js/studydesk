<?php
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validate.php';
require_once __DIR__ . '/../config/db.php';

require_user_api();

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? '';
    if (!csrf_verify($csrfToken)) {
        json_response(null, 'Invalid CSRF token.', false, 403);
    }
}

try {
    if ($method === 'GET') {
        // Removed date_of_birth from SELECT
        $stmt = $pdo->prepare("SELECT username, email, full_name, academic_level, gender, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        json_response($profile, 'Profile fetched.', true, 200);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            json_response(null, 'Invalid JSON payload.', false, 400);
        }

        $action = $input['action'] ?? '';

        if ($action === 'update_profile') {
            $fullName = validate_max_length($input['full_name'] ?? '', 100, 'Full Name');
            if (!$fullName) json_response(null, 'Full Name is required.', false, 400);

            $academicLevel = validate_enum($input['academic_level'] ?? '', ['school', 'plus_two', 'bachelor', 'master', 'others'], 'Academic Level');
            $gender = validate_enum($input['gender'] ?? '', ['male', 'female', 'other'], 'Gender');

            // Removed date_of_birth from UPDATE
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, academic_level = ?, gender = ? WHERE id = ?");
            $stmt->execute([$fullName, $academicLevel, $gender, $userId]);

            json_response(null, 'Profile updated successfully.', true, 200);

        } elseif ($action === 'change_password') {
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';

            if (!$currentPassword || !$newPassword) {
                json_response(null, 'Current and new password are required.', false, 400);
            }

            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,64}$/';
            if (!preg_match($passwordRegex, $newPassword)) {
                json_response(null, 'New password must be 8-64 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.', false, 400);
            }

            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($currentPassword, $user['password'])) {
                json_response(null, 'Incorrect current password.', false, 400);
            }

            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$newHash, $userId]);

            json_response(null, 'Password updated successfully.', true, 200);
        }
    }
} catch (Exception $e) {
    json_error($e);
}