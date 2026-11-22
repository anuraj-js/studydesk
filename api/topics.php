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
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) json_response(null, 'Invalid JSON payload.', false, 400);

        $action = $input['action'] ?? '';
        $examId = (int)($input['exam_id'] ?? 0);

        // Verify the exam belongs to the current user
        $checkStmt = $pdo->prepare("SELECT id FROM exams WHERE id = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$examId, $userId]);
        if (!$checkStmt->fetch()) {
            json_response(null, 'Exam not found or unauthorized.', false, 403);
        }

        if ($action === 'add') {
            $text = validate_max_length($input['text'] ?? '', 500, 'Topic text');
            if (!$text) json_response(null, 'Topic text is required.', false, 400);

            $stmt = $pdo->prepare("INSERT INTO topics (exam_id, text) VALUES (?, ?)");
            $stmt->execute([$examId, $text]);
            json_response(['id' => $pdo->lastInsertId()], 'Topic added.', true, 200);

        } elseif ($action === 'toggle') {
            $topicId = (int)($input['id'] ?? 0);
            
            $stmt = $pdo->prepare("SELECT completed FROM topics WHERE id = ? AND exam_id = ? LIMIT 1");
            $stmt->execute([$topicId, $examId]);
            $topic = $stmt->fetch();

            if (!$topic) json_response(null, 'Topic not found.', false, 404);

            $newStatus = $topic['completed'] ? 0 : 1;
            // Added exam_id to the WHERE clause for safety
            $updateStmt = $pdo->prepare("UPDATE topics SET completed = ? WHERE id = ? AND exam_id = ?");
            $updateStmt->execute([$newStatus, $topicId, $examId]);

            json_response(['completed' => (bool)$newStatus], 'Topic toggled.', true, 200);

        } elseif ($action === 'delete') {
            $topicId = (int)($input['id'] ?? 0);

            $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ? AND exam_id = ?");
            $stmt->execute([$topicId, $examId]);

            if ($stmt->rowCount() === 0) {
                json_response(null, 'Topic not found or already deleted.', false, 404);
            }
            json_response(null, 'Topic deleted.', true, 200);
        }
    }
} catch (Exception $e) {
    json_error($e);
}