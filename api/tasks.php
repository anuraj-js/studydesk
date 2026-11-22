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
        $stmt = $pdo->prepare("SELECT id, title, priority, due_date, completed, created_at FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll();
        
        foreach ($tasks as &$task) {
            $task['completed'] = (bool)$task['completed'];
        }
        
        json_response($tasks, 'Tasks fetched successfully.', true, 200);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            json_response(null, 'Invalid JSON payload.', false, 400);
        }

        $action = $input['action'] ?? 'create';

        if ($action === 'create') {
            $title = validate_max_length($input['title'] ?? '', 255, 'Task title');
            if (!$title) json_response(null, 'Task title is required.', false, 400);

            $priority = validate_enum($input['priority'] ?? 'medium', ['high', 'medium', 'low'], 'Priority');
            // Applied future_or_today validation
            $dueDate = validate_future_or_today(validate_date($input['due_date'] ?? '', 'Due Date', false), 'Due Date');

            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, priority, due_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $title, $priority, $dueDate]);
            $newId = $pdo->lastInsertId();

            json_response(['id' => $newId], 'Task created successfully.', true, 200);

        } elseif ($action === 'update') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) json_response(null, 'Task ID is required.', false, 400);

            $title = validate_max_length($input['title'] ?? '', 255, 'Task title');
            if (!$title) json_response(null, 'Task title is required.', false, 400);

            $priority = validate_enum($input['priority'] ?? 'medium', ['high', 'medium', 'low'], 'Priority');
            // Applied future_or_today validation
            $dueDate = validate_future_or_today(validate_date($input['due_date'] ?? '', 'Due Date', false), 'Due Date');

            $stmt = $pdo->prepare("UPDATE tasks SET title = ?, priority = ?, due_date = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $priority, $dueDate, $id, $userId]);

            if ($stmt->rowCount() === 0) {
                // Could be 0 if data is identical, so we check if it exists
                $check = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
                $check->execute([$id, $userId]);
                if (!$check->fetch()) {
                    json_response(null, 'Task not found or unauthorized.', false, 404);
                }
            }

            json_response(null, 'Task updated successfully.', true, 200);

        } elseif ($action === 'toggle') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) json_response(null, 'Task ID is required.', false, 400);
            
            $stmt = $pdo->prepare("SELECT completed FROM tasks WHERE id = ? AND user_id = ? LIMIT 1");
            $stmt->execute([$id, $userId]);
            $task = $stmt->fetch();

            if (!$task) {
                json_response(null, 'Task not found.', false, 404);
            }

            $newStatus = $task['completed'] ? 0 : 1;
            $updateStmt = $pdo->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
            $updateStmt->execute([$newStatus, $id, $userId]);

            json_response(['completed' => (bool)$newStatus], 'Task toggled.', true, 200);

        } elseif ($action === 'delete') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) json_response(null, 'Task ID is required.', false, 400);

            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);

            if ($stmt->rowCount() === 0) {
                json_response(null, 'Task not found or already deleted.', false, 404);
            }

            json_response(null, 'Task deleted successfully.', true, 200);
        }
    }
} catch (Exception $e) {
    json_error($e);
}