<?php
// api/admin/user_profile.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

if (empty($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Forbidden", "data" => null]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$targetUserId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($targetUserId <= 0) {
    echo json_encode(["success" => false, "message" => "User ID required", "data" => null]);
    exit();
}

try {
    // Fetch user profile (excluding password)
    $sql = "SELECT id, username, email, role, academic_level, phone, address, dob, gender, theme, dark_mode, timezone, created_at 
            FROM users 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $targetUserId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["success" => false, "message" => "User not found", "data" => null]);
        exit();
    }

    // Activity summary counts
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND activity_type = 'task_completed'");
    $stmt->execute(["user_id" => $targetUserId]);
    $tasksCompleted = (int)$stmt->fetch()["count"];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND activity_type = 'exam_created'");
    $stmt->execute(["user_id" => $targetUserId]);
    $examsCreated = (int)$stmt->fetch()["count"];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND activity_type = 'topic_completed'");
    $stmt->execute(["user_id" => $targetUserId]);
    $topicsCompleted = (int)$stmt->fetch()["count"];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND activity_type = 'exam_completed'");
    $stmt->execute(["user_id" => $targetUserId]);
    $examsCompleted = (int)$stmt->fetch()["count"];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND activity_type = 'pomodoro_completed'");
    $stmt->execute(["user_id" => $targetUserId]);
    $pomodoroSessions = (int)$stmt->fetch()["count"];

    // Total activities
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $targetUserId]);
    $totalActivities = (int)$stmt->fetch()["count"];

    // Check if user has a quick note
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM quick_notes WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $targetUserId]);
    $hasQuickNote = (int)$stmt->fetch()["count"] > 0;

    // Last active timestamp
    $stmt = $pdo->prepare("SELECT MAX(created_at) as last_active FROM activity_history WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $targetUserId]);
    $lastActiveResult = $stmt->fetch();
    $lastActive = $lastActiveResult["last_active"];

    // Current counts
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $targetUserId]);
    $activeTasks = (int)$stmt->fetch()["count"];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM exams WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $targetUserId]);
    $activeExams = (int)$stmt->fetch()["count"];

    echo json_encode([
        "success" => true,
        "message" => "User profile fetched successfully",
        "data" => [
            "user" => $user,
            "summary" => [
                "tasks_completed" => $tasksCompleted,
                "exams_created" => $examsCreated,
                "topics_completed" => $topicsCompleted,
                "exams_completed" => $examsCompleted,
                "pomodoro_sessions" => $pomodoroSessions,
                "total_activities" => $totalActivities,
                "has_quick_note" => $hasQuickNote,
                "last_active" => $lastActive,
                "active_tasks" => $activeTasks,
                "active_exams" => $activeExams
            ]
        ]
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong. Please try again.",
        "data" => null
    ]);
    exit();
}
?>