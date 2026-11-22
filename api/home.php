<?php
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(null, 'Invalid request method.', false, 400);
}

require_user_api();

$userId = $_SESSION['user_id'];

try {
    // 1. Fetch Tasks
    $stmt = $pdo->prepare("SELECT id, title, priority, due_date, completed FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $allTasks = $stmt->fetchAll();

    $pendingTasks = array_filter($allTasks, fn($t) => !$t['completed']);
    $doneTasks = array_filter($allTasks, fn($t) => (bool)$t['completed']);
    
    // 2. Fetch Exams
    $stmt = $pdo->prepare("SELECT id, name, subject, exam_date as date, exam_time as time FROM exams WHERE user_id = ? ORDER BY exam_date ASC, exam_time ASC");
    $stmt->execute([$userId]);
    $allExams = $stmt->fetchAll();

    // Find next exam (using UTC)
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $nextExam = null;
    $upcomingExams = [];
    
    foreach ($allExams as $exam) {
        // Treat the DB strings as UTC (since PHP is UTC)
        $examDateTime = new DateTime($exam['date'] . ' ' . $exam['time'], new DateTimeZone('UTC'));
        if ($examDateTime >= $now) {
            if (!$nextExam) $nextExam = $exam;
            $upcomingExams[] = $exam;
        }
    }

    // 3. Fetch Pomodoro Sessions for today (UTC)
    $today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
    $stmt = $pdo->prepare("SELECT count FROM pomodoro_sessions WHERE user_id = ? AND session_date = ? LIMIT 1");
    $stmt->execute([$userId, $today]);
    $sessions = $stmt->fetch();
    $todaySessions = $sessions ? (int)$sessions['count'] : 0;

    // 4. Format the response
    $data = [
        'stats' => [
            'pending_tasks' => count($pendingTasks),
            'done_tasks' => count($doneTasks),
            'next_exam_date' => $nextExam ? $nextExam['date'] : null,
            'today_sessions' => $todaySessions
        ],
        'recent_tasks' => array_slice($pendingTasks, 0, 4),
        'upcoming_exams' => array_slice($upcomingExams, 0, 4),
        'calendar_events' => [
            'tasks' => array_map(fn($t) => ['date' => $t['due_date'], 'title' => $t['title'], 'priority' => $t['priority'], 'completed' => (bool)$t['completed']], $allTasks),
            'exams' => array_map(fn($e) => ['date' => $e['date'], 'subject' => $e['subject'], 'name' => $e['name']], $allExams)
        ]
    ];

    json_response($data, 'Dashboard data fetched successfully.', true, 200);

} catch (Exception $e) {
    json_error($e);
}