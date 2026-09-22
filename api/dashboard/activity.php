<?php
// api/dashboard/activity.php

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
    // Delete rows 90 or more days old (date-only comparison to avoid time component issues)
    $stmt = $pdo->prepare("DELETE FROM activity_history WHERE user_id = :user_id AND DATE(created_at) <= DATE(DATE_SUB(NOW(), INTERVAL 90 DAY))");
    $stmt->execute(["user_id" => $userId]);

    $sql = "SELECT activity_type, related_item_name, additional_details, created_at FROM activity_history WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["user_id" => $userId]);
    $activities = $stmt->fetchAll();

    foreach ($activities as &$activity) {
        $datetime = new DateTime($activity['created_at']);
        $activity['formatted_time'] = $datetime->format('Y-m-d H:i:s');
        $activity['text'] = $activity['activity_type'] . ': ' . $activity['related_item_name'];
        $activity['type'] = $activity['activity_type'];
    }
    unset($activity);

    echo json_encode([
        "success" => true,
        "message" => "Activity fetched successfully",
        "data" => ["activities" => $activities]
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>