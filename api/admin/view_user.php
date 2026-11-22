<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../config/db.php';

require_admin_api();

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    json_response(null, 'User ID is required.', false, 400);
}

try {
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, academic_level, gender, created_at, is_active FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(null, 'User not found.', false, 404);
    }

    $stmt = $pdo->prepare("SELECT id, title, priority, due_date, completed FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $tasks = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id, name, subject, exam_date as date, exam_time as time FROM exams WHERE user_id = ? ORDER BY exam_date ASC");
    $stmt->execute([$userId]);
    $exams = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id, title, updated_at FROM notes WHERE user_id = ? ORDER BY updated_at DESC");
    $stmt->execute([$userId]);
    $notes = $stmt->fetchAll();

    $data = [
        'user' => $user,
        'tasks' => $tasks,
        'exams' => $exams,
        'notes' => $notes
    ];

    json_response($data, 'User details fetched.', true, 200);

} catch (Exception $e) {
    json_error($e);
}