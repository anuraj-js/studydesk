<?php
// api/admin/stats.php

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

try {
    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $usersCount = (int)$stmt->fetch()["count"];

    // Total tasks
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks");
    $stmt->execute();
    $tasksCount = (int)$stmt->fetch()["count"];

    // Total exams
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM exams");
    $stmt->execute();
    $examsCount = (int)$stmt->fetch()["count"];

    // Total activity
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_history");
    $stmt->execute();
    $activityCount = (int)$stmt->fetch()["count"];

    echo json_encode([
        "success" => true,
        "message" => "Stats fetched successfully",
        "data" => [
            "users" => $usersCount,
            "tasks" => $tasksCount,
            "exams" => $examsCount,
            "activity" => $activityCount
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