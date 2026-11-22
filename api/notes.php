<?php
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validate.php';
require_once __DIR__ . '/../config/db.php';

require_user_api();

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Read JSON body FIRST so we can check for _csrf inside it
$input = json_decode(file_get_contents('php://input'), true);

if ($method !== 'GET') {
    $headers = getallheaders();
    // Look for the token in the header first, then fall back to the body
    $csrfToken = $headers['X-CSRF-Token'] ?? ($input['_csrf'] ?? '');
    
    if (!csrf_verify($csrfToken)) {
        json_response(null, 'Invalid CSRF token.', false, 403);
    }
}

try {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT id, title, body, created_at, updated_at FROM notes WHERE user_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$userId]);
        json_response($stmt->fetchAll(), 'Notes fetched successfully.', true, 200);
    } 
    elseif ($method === 'POST') {
        if (!$input) json_response(null, 'Invalid JSON payload.', false, 400);

        $action = $input['action'] ?? '';

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, body) VALUES (?, 'Untitled Note', '')");
            $stmt->execute([$userId]);
            $newId = $pdo->lastInsertId();

            $fetchStmt = $pdo->prepare("SELECT id, title, body, created_at, updated_at FROM notes WHERE id = ?");
            $fetchStmt->execute([$newId]);
            json_response($fetchStmt->fetch(), 'Note created.', true, 200);

        } elseif ($action === 'update') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) json_response(null, 'Note ID required.', false, 400);

            $title = validate_max_length($input['title'] ?? '', 255, 'Note Title');
            if ($title === '') $title = 'Untitled Note';

            $body = $input['body'] ?? ''; // No length limit on MEDIUMTEXT

            $stmt = $pdo->prepare("UPDATE notes SET title = ?, body = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $body, $id, $userId]);

            if ($stmt->rowCount() === 0) {
                $checkStmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
                $checkStmt->execute([$id, $userId]);
                if (!$checkStmt->fetch()) {
                    json_response(null, 'Note not found or unauthorized.', false, 404);
                }
            }
            json_response(null, 'Note saved.', true, 200);

        } elseif ($action === 'delete') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) json_response(null, 'Note ID required.', false, 400);

            $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);

            if ($stmt->rowCount() === 0) {
                json_response(null, 'Note not found or already deleted.', false, 404);
            }
            json_response(null, 'Note deleted.', true, 200);
        }
    }
} catch (Exception $e) {
    json_error($e);
}