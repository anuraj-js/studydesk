<?php
// api/pomodoro/log.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/validator.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Null-safe input handling
$taskName = isset($data["task_name"]) ? (trim($data["task_name"]) ?: "Untitled") : "Untitled";
$totalSeconds = isset($data["total_seconds"]) ? (int)$data["total_seconds"] : 0;

// Validate task name length
if (!isWithinLength($taskName, 255)) {
    echo json_encode(["success" => false, "message" => "Task name must be 255 characters or less", "data" => null]);
    exit();
}

// Validate duration: must be > 0 and <= 3 hours (10800 seconds)
if ($totalSeconds <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid duration", "data" => null]);
    exit();
}

if ($totalSeconds > 10800) {
    echo json_encode(["success" => false, "message" => "Duration cannot exceed 3 hours", "data" => null]);
    exit();
}

try {
    $sql = "INSERT INTO activity_history (user_id, activity_type, related_item_name, additional_details) 
            VALUES (:user_id, 'pomodoro_completed', :item_name, :details)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "user_id" => $userId,
        "item_name" => $taskName,
        "details" => json_encode(["total_seconds" => $totalSeconds])
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Session logged successfully",
        "data" => null
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>