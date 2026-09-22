<?php
// api/activity/fetch.php

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

    // Fetch remaining rows (newest first, no limit)
    $stmt = $pdo->prepare("SELECT id, activity_type AS type, related_item_name AS item_name, additional_details AS details, created_at FROM activity_history WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(["user_id" => $userId]);
    $activities = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "message" => empty($activities) ? "No activity found" : "Activity fetched successfully",
        "data" => ["activities" => $activities]
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