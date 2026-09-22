<?php
// api/dashboard/calendar.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

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
    // Fetch tasks
    $stmt = $pdo->prepare("SELECT id, title, due_date FROM tasks WHERE user_id = :user_id ORDER BY due_date ASC");
    $stmt->execute(["user_id" => $userId]);
    $tasks = $stmt->fetchAll();

    // Fetch exams
    $stmt = $pdo->prepare("SELECT id, exam_name, exam_date FROM exams WHERE user_id = :user_id ORDER BY exam_date ASC");
    $stmt->execute(["user_id" => $userId]);
    $exams = $stmt->fetchAll();

    // Group by date
    $events = [];

    foreach ($tasks as $task) {
        $date = $task["due_date"];
        if (!isset($events[$date])) {
            $events[$date] = [];
        }
        $events[$date][] = [
            "type" => "task",
            "title" => $task["title"],
            "id" => $task["id"]
        ];
    }

    foreach ($exams as $exam) {
        $date = $exam["exam_date"];
        if (!isset($events[$date])) {
            $events[$date] = [];
        }
        $events[$date][] = [
            "type" => "exam",
            "title" => $exam["exam_name"],
            "id" => $exam["id"]
        ];
    }

    // Sort events by date
    ksort($events);

    echo json_encode([
        "success" => true,
        "message" => "Calendar fetched successfully",
        "data" => ["events" => $events]
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