<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../config/db.php';

require_admin_api();

try {
    // 1. Global Stats
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalTasks = (int)$pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
    $totalExams = (int)$pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
    $totalSessions = (int)$pdo->query("SELECT SUM(count) FROM pomodoro_sessions")->fetchColumn();

    // 2. Recent Signups (last 5 users)
    $stmt = $pdo->query("SELECT id, username, email, full_name, created_at, is_active FROM users ORDER BY created_at DESC LIMIT 5");
    $recentSignups = $stmt->fetchAll();

    // 3. Recent Admin Actions (last 5 logs)
    $stmt = $pdo->query("
        SELECT l.id, l.action, l.target_id, l.target_type, l.created_at, a.username as admin_name
        FROM admin_audit_logs l
        JOIN admins a ON l.admin_id = a.id
        ORDER BY l.created_at DESC LIMIT 5
    ");
    $recentActions = $stmt->fetchAll();

    $data = [
        'stats' => [
            'total_users' => $totalUsers,
            'total_tasks' => $totalTasks,
            'total_exams' => $totalExams,
            'total_sessions' => $totalSessions ?: 0
        ],
        'recent_signups' => $recentSignups,
        'recent_actions' => $recentActions
    ];

    json_response($data, 'Admin dashboard data fetched.', true, 200);

} catch (Exception $e) {
    json_error($e);
}