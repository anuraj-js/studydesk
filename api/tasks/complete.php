<?php
// api/tasks/complete.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];
$taskId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($taskId <= 0) {
    echo json_encode(["success" => false, "message" => "Task ID required", "data" => null]);
    exit();
}

// Fetch task
$sql = "SELECT id, title, type, due_date, created_at FROM tasks WHERE id = :id AND user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(["id" => $taskId, "user_id" => $userId]);
$task = $stmt->fetch();

if (!$task) {
    echo json_encode(["success" => false, "message" => "Task not found", "data" => null]);
    exit();
}

try {
    $pdo->beginTransaction();

    // Insert into activity_history
    $sql = "INSERT INTO activity_history (user_id, activity_type, related_item_name, additional_details) 
            VALUES (:user_id, 'task_completed', :item_name, :details)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "user_id" => $userId,
        "item_name" => $task["title"],
        "details" => json_encode([
            "task_type" => $task["type"],
            "due_date" => $task["due_date"],
            "created_date" => $task["created_at"]
        ])
    ]);

    // Delete task
    $sql = "DELETE FROM tasks WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $taskId]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Task completed successfully",
        "data" => null
    ]);
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Failed to complete task", "data" => null]);
    exit();
}
?>