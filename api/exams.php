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
        $stmt = $pdo->prepare("SELECT id, name, subject, exam_date as date, exam_time as time FROM exams WHERE user_id = ? ORDER BY exam_date ASC, exam_time ASC");
        $stmt->execute([$userId]);
        $exams = $stmt->fetchAll();

        foreach ($exams as &$exam) {
            $topicStmt = $pdo->prepare("SELECT id, text, completed FROM topics WHERE exam_id = ? ORDER BY created_at ASC");
            $topicStmt->execute([$exam['id']]);
            $topics = $topicStmt->fetchAll();
            
            foreach ($topics as &$topic) {
                $topic['completed'] = (bool)$topic['completed'];
            }
            $exam['topics'] = $topics;
        }

        json_response($exams, 'Exams fetched successfully.', true, 200);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) json_response(null, 'Invalid JSON payload.', false, 400);

        $action = $input['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $id = (int)($input['id'] ?? 0);
            if ($action === 'update' && !$id) json_response(null, 'Exam ID is required.', false, 400);

            $name = validate_max_length($input['name'] ?? '', 255, 'Exam name');
            if (!$name) json_response(null, 'Exam name is required.', false, 400);

            $subject = validate_max_length($input['subject'] ?? '', 255, 'Subject');
            if (!$subject) json_response(null, 'Subject is required.', false, 400);

            $date = validate_date($input['date'] ?? '', 'Exam Date');
            $time = validate_time($input['time'] ?? '', 'Exam Time');
            
            // Combined check: Prevents today's exam at a past time
            validate_future_datetime($date, $time, 'Exam date and time');

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO exams (user_id, name, subject, exam_date, exam_time) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $name, $subject, $date, $time]);
                json_response(['id' => $pdo->lastInsertId()], 'Exam created successfully.', true, 200);
            } else {
                $stmt = $pdo->prepare("UPDATE exams SET name = ?, subject = ?, exam_date = ?, exam_time = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$name, $subject, $date, $time, $id, $userId]);
                
                if ($stmt->rowCount() === 0) {
                    $check = $pdo->prepare("SELECT id FROM exams WHERE id = ? AND user_id = ?");
                    $check->execute([$id, $userId]);
                    if (!$check->fetch()) json_response(null, 'Exam not found or unauthorized.', false, 404);
                }
                json_response(null, 'Exam updated successfully.', true, 200);
            }

        } elseif ($action === 'delete') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) json_response(null, 'Exam ID is required.', false, 400);

            $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);

            if ($stmt->rowCount() === 0) {
                json_response(null, 'Exam not found or already deleted.', false, 404);
            }

            json_response(null, 'Exam deleted successfully.', true, 200);
        }
    }
} catch (Exception $e) {
    json_error($e);
}