<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../includes/admin_layout.php';
require_once __DIR__ . '/../../config/db.php';

require_admin_api();

$adminId = $_SESSION['user_id'];
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
        $stmt = $pdo->query("SELECT id, username, email, full_name, created_at FROM admins ORDER BY created_at DESC");
        $admins = $stmt->fetchAll();
        json_response($admins, 'Admins fetched.', true, 200);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            json_response(null, 'Invalid JSON payload.', false, 400);
        }

        $action = $input['action'] ?? '';

        if ($action === 'create') {
            $fullName = validate_max_length($input['full_name'] ?? '', 100, 'Full Name');
            if (!$fullName) json_response(null, 'Full Name is required.', false, 400);

            $username = validate_max_length($input['username'] ?? '', 50, 'Username');
            if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
                json_response(null, 'Username must be 3-50 characters long and contain only letters, numbers, and underscores.', false, 400);
            }

            $email = validate_max_length($input['email'] ?? '', 150, 'Email');
            $email = validate_email($email, 'Email');

            $password = $input['password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? $password; // Fallback to password if not sent

            if ($password !== $confirmPassword) {
                json_response(null, 'Passwords do not match.', false, 400);
            }

            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,64}$/';
            if (!preg_match($passwordRegex, $password)) {
                json_response(null, 'Password must be 8-64 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.', false, 400);
            }

            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                json_response(null, 'Username or email already exists.', false, 400);
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("INSERT INTO admins (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$username, $email, $hashedPassword, $fullName]);
            
            $newAdminId = $pdo->lastInsertId();
            log_admin_action($adminId, "Created new admin '{$username}'", $newAdminId, 'admin');

            json_response(null, 'Admin created successfully.', true, 200);

        } elseif ($action === 'delete') {
            // ... [keep the delete logic exactly as it was] ...
            $targetAdminId = (int)($input['admin_id'] ?? 0);
            if (!$targetAdminId) json_response(null, 'Admin ID is required.', false, 400);

            if ($targetAdminId === $adminId) {
                json_response(null, 'You cannot delete your own account.', false, 403);
            }

            $pdo->beginTransaction();
            try {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM admins FOR UPDATE")->fetchColumn();
                if ($count <= 1) {
                    $pdo->rollBack();
                    json_response(null, 'System integrity error: Cannot delete the last remaining admin.', false, 403);
                }

                $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ? LIMIT 1");
                $stmt->execute([$targetAdminId]);
                $targetAdmin = $stmt->fetch();

                if (!$targetAdmin) {
                    $pdo->rollBack();
                    json_response(null, 'Admin not found.', false, 404);
                }

                $deleteStmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                $deleteStmt->execute([$targetAdminId]);

                if ($deleteStmt->rowCount() === 0) {
                    $pdo->rollBack();
                    json_response(null, 'Failed to delete admin.', false, 500);
                }

                log_admin_action($adminId, "Deleted admin '{$targetAdmin['username']}'", $targetAdminId, 'admin');
                
                $pdo->commit();
                json_response(null, 'Admin deleted successfully.', true, 200);

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }
} catch (Exception $e) {
    json_error($e);
}