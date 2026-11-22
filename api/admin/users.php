<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/admin_layout.php'; // For log_admin_action()
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
        $stmt = $pdo->query("SELECT id, username, email, full_name, created_at, is_active FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();
        json_response($users, 'Users fetched.', true, 200);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            json_response(null, 'Invalid JSON payload.', false, 400);
        }

        $action = $input['action'] ?? '';
        $targetUserId = (int)($input['user_id'] ?? 0);

        if (!$targetUserId) {
            json_response(null, 'User ID is required.', false, 400);
        }

        // Check if target user exists
        $stmt = $pdo->prepare("SELECT username, is_active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            json_response(null, 'User not found.', false, 404);
        }

        if ($action === 'toggle_status') {
            $newStatus = $targetUser['is_active'] ? 0 : 1;
            
            $pdo->beginTransaction();
            try {
                $updateStmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
                $updateStmt->execute([$newStatus, $targetUserId]);

                $statusText = $newStatus ? 'Activated' : 'Deactivated';
                log_admin_action($adminId, "{$statusText} user '{$targetUser['username']}'", $targetUserId, 'user');
                
                $pdo->commit();
                json_response(null, "User {$statusText}.", true, 200);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

        } elseif ($action === 'delete') {
            $pdo->beginTransaction();
            try {
                $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $deleteStmt->execute([$targetUserId]);

                if ($deleteStmt->rowCount() === 0) {
                    $pdo->rollBack();
                    json_response(null, 'User not found or already deleted.', false, 404);
                }

                log_admin_action($adminId, "Deleted user '{$targetUser['username']}'", $targetUserId, 'user');
                
                $pdo->commit();
                json_response(null, 'User deleted successfully.', true, 200);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }
} catch (Exception $e) {
    json_error($e);
}