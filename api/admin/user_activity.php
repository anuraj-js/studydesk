<?php
// api/admin/user_activity.php

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
    // Search and filter parameters
    $search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
    $filterUserId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
    $type = isset($_GET["type"]) ? trim($_GET["type"]) : "all";
    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
    $perPage = 20;

    if ($page < 1) {
        $page = 1;
    }

    // Build WHERE clause
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(ah.related_item_name LIKE :search OR ah.activity_type LIKE :search)";
        $params[":search"] = "%" . $search . "%";
    }

    if ($filterUserId > 0) {
        $where[] = "ah.user_id = :user_id";
        $params[":user_id"] = $filterUserId;
    }

    $validTypes = ["task_completed", "exam_created", "topic_completed", "exam_completed", "pomodoro_completed"];
    if ($type !== "all" && in_array($type, $validTypes)) {
        $where[] = "ah.activity_type = :type";
        $params[":type"] = $type;
    }

    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // Count total activities
    $countSql = "SELECT COUNT(*) as total FROM activity_history ah " . $whereClause;
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalActivities = (int)$stmt->fetch()["total"];

    $totalPages = $totalActivities > 0 ? (int)ceil($totalActivities / $perPage) : 1;

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $perPage;

    // Fetch activities with username
    $sql = "SELECT ah.id, ah.user_id, u.username, ah.activity_type, ah.related_item_name, ah.additional_details, ah.created_at 
            FROM activity_history ah 
            JOIN users u ON ah.user_id = u.id 
            " . $whereClause . " 
            ORDER BY ah.created_at DESC 
            LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll();

    // Decode additional_details JSON for each activity
    foreach ($activities as &$activity) {
        if (isset($activity["additional_details"]) && is_string($activity["additional_details"])) {
            $decoded = json_decode($activity["additional_details"], true);
            $activity["additional_details"] = $decoded !== null ? $decoded : [];
        }
    }
    unset($activity);

    echo json_encode([
        "success" => true,
        "message" => "Activity fetched successfully",
        "data" => [
            "activities" => $activities,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $perPage,
                "total" => $totalActivities,
                "total_pages" => $totalPages
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