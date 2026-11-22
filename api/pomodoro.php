<?php
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
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
        $stmt = $pdo->prepare("SELECT work_min, work_sec, short_min, short_sec, long_min, long_sec, long_break_after FROM pomodoro_settings WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $settings = $stmt->fetch();

        if (!$settings) {
            $insertStmt = $pdo->prepare("INSERT INTO pomodoro_settings (user_id) VALUES (?)");
            $insertStmt->execute([$userId]);
            $settings = [
                'work_min' => 25, 'work_sec' => 0,
                'short_min' => 5, 'short_sec' => 0,
                'long_min' => 15, 'long_sec' => 0,
                'long_break_after' => 4
            ];
        }

        $today = date('Y-m-d'); // PHP is now UTC
        $stmt = $pdo->prepare("SELECT count FROM pomodoro_sessions WHERE user_id = ? AND session_date = ? LIMIT 1");
        $stmt->execute([$userId, $today]);
        $session = $stmt->fetch();
        $todayCount = $session ? (int)$session['count'] : 0;

        json_response(['settings' => $settings, 'today_sessions' => $todayCount], 'Data fetched.', true, 200);

    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) json_response(null, 'Invalid JSON payload.', false, 400);

        $action = $input['action'] ?? '';

        if ($action === 'settings') {
            $workMin = max(0, min(90, (int)($input['work_min'] ?? 25)));
            $workSec = max(0, min(59, (int)($input['work_sec'] ?? 0)));
            $shortMin = max(0, min(30, (int)($input['short_min'] ?? 5)));
            $shortSec = max(0, min(59, (int)($input['short_sec'] ?? 0)));
            $longMin = max(0, min(60, (int)($input['long_min'] ?? 15)));
            $longSec = max(0, min(59, (int)($input['long_sec'] ?? 0)));
            $longAfter = max(1, min(10, (int)($input['long_break_after'] ?? 4)));

            $stmt = $pdo->prepare("
                UPDATE pomodoro_settings 
                SET work_min = ?, work_sec = ?, short_min = ?, short_sec = ?, long_min = ?, long_sec = ?, long_break_after = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$workMin, $workSec, $shortMin, $shortSec, $longMin, $longSec, $longAfter, $userId]);

            json_response(null, 'Settings saved.', true, 200);

        } elseif ($action === 'record') {
            $today = date('Y-m-d'); // UTC
            
            // Atomic Upsert to prevent race conditions
            $stmt = $pdo->prepare("
                INSERT INTO pomodoro_sessions (user_id, session_date, count) 
                VALUES (?, ?, 1) 
                ON DUPLICATE KEY UPDATE count = count + 1
            ");
            $stmt->execute([$userId, $today]);

            // Fetch the new count to return to the frontend
            $fetchStmt = $pdo->prepare("SELECT count FROM pomodoro_sessions WHERE user_id = ? AND session_date = ?");
            $fetchStmt->execute([$userId, $today]);
            $newCount = (int)$fetchStmt->fetchColumn();

            json_response(['count' => $newCount], 'Session recorded.', true, 200);

        } elseif ($action === 'reset_sessions') {
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("DELETE FROM pomodoro_sessions WHERE user_id = ? AND session_date = ?");
            $stmt->execute([$userId, $today]);

            json_response(['count' => 0], 'Sessions reset.', true, 200);
        }
    }
} catch (Exception $e) {
    json_error($e);
}