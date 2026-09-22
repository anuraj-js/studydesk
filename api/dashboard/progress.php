<?php
// api/dashboard/progress.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/timezone.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

try {
    applyUserTimezone($pdo, $userId);

    function countActivity($pdo, $userId, $type) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND activity_type = :type");
        $stmt->execute(["user_id" => $userId, "type" => $type]);
        $result = $stmt->fetch();
        return (int)$result["count"];
    }

    $tasksCompleted   = countActivity($pdo, $userId, 'task_completed');
    $examsCreated     = countActivity($pdo, $userId, 'exam_created');
    $topicsCompleted  = countActivity($pdo, $userId, 'topic_completed');
    $examsCompleted   = countActivity($pdo, $userId, 'exam_completed');
    $pomodoroSessions = countActivity($pdo, $userId, 'pomodoro_completed');

    $stmt = $pdo->prepare("SELECT DATE(created_at) as date FROM activity_history WHERE user_id = :user_id AND DATE(created_at) >= DATE(DATE_SUB(NOW(), INTERVAL 90 DAY)) GROUP BY DATE(created_at) ORDER BY date DESC");
    $stmt->execute(["user_id" => $userId]);
    $rows  = $stmt->fetchAll();
    $dates = array_column($rows, 'date');

    $streak    = 0;
    $today     = date('Y-m-d');
    $checkDate = $today;

    while (in_array($checkDate, $dates)) {
        $streak++;
        $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
    }

    $yesterday            = date('Y-m-d', strtotime('-1 day'));
    $hasActivityYesterday = in_array($yesterday, $dates);
    $hasActivityToday     = in_array($today, $dates);

    $longestStreak = 0;
    $longestStart  = null;
    $longestEnd    = null;

    if (!empty($dates)) {
        $ascDates = $dates;
        sort($ascDates);

        $currentStreak = 1;
        $currentStart  = $ascDates[0];
        $currentEnd    = $ascDates[0];

        for ($i = 1; $i < count($ascDates); $i++) {
            $prev = new DateTime($ascDates[$i - 1]);
            $curr = new DateTime($ascDates[$i]);
            $diff = $prev->diff($curr)->days;

            if ($diff == 1) {
                $currentStreak++;
                $currentEnd = $ascDates[$i];
            } else {
                if ($currentStreak > $longestStreak) {
                    $longestStreak = $currentStreak;
                    $longestStart  = $currentStart;
                    $longestEnd    = $currentEnd;
                }
                $currentStreak = 1;
                $currentStart  = $ascDates[$i];
                $currentEnd    = $ascDates[$i];
            }
        }

        if ($currentStreak > $longestStreak) {
            $longestStreak = $currentStreak;
            $longestStart  = $currentStart;
            $longestEnd    = $currentEnd;
        }
    }

    $longestRange = null;
    if ($longestStart && $longestEnd) {
        $startObj = new DateTime($longestStart);
        $endObj   = new DateTime($longestEnd);
        if ($longestStart === $longestEnd) {
            $longestRange = $startObj->format('M j, Y');
        } else {
            $longestRange = $startObj->format('M j') . ' – ' . $endObj->format('M j, Y');
        }
    }

    $stmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM activity_history WHERE user_id = :user_id AND DATE(created_at) >= DATE(DATE_SUB(NOW(), INTERVAL 90 DAY)) GROUP BY DATE(created_at) ORDER BY count DESC, date DESC LIMIT 1");
    $stmt->execute(["user_id" => $userId]);
    $mostProductive = $stmt->fetch();

    $mostProductiveDay = null;
    if ($mostProductive) {
        $dateObj = new DateTime($mostProductive["date"]);
        $mostProductiveDay = [
            "date"  => $mostProductive["date"],
            "day"   => $dateObj->format('l, M j, Y'),
            "count" => (int)$mostProductive["count"]
        ];
    }

    $stmt = $pdo->prepare("SELECT DATE(created_at) as last_active_date FROM activity_history WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(["user_id" => $userId]);
    $lastActive = $stmt->fetch();

    $lastActiveDay = null;
    if ($lastActive) {
        $dateObj = new DateTime($lastActive["last_active_date"]);
        $lastActiveDay = [
            "date" => $lastActive["last_active_date"],
            "day"  => $dateObj->format('l, M j, Y')
        ];
    }

    echo json_encode([
        "success" => true,
        "message" => "Progress fetched successfully",
        "data"    => [
            "tasks_completed"        => $tasksCompleted,
            "exams_created"          => $examsCreated,
            "topics_completed"       => $topicsCompleted,
            "exams_completed"        => $examsCompleted,
            "pomodoro_sessions"      => $pomodoroSessions,
            "streak"                 => $streak,
            "has_activity_today"     => $hasActivityToday,
            "has_activity_yesterday" => $hasActivityYesterday,
            "longest_streak"         => $longestStreak,
            "longest_streak_range"   => $longestRange,
            "most_productive_day"    => $mostProductiveDay,
            "last_active"            => $lastActiveDay
        ]
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong. Please try again.",
        "data"    => null
    ]);
    exit();
}
?>