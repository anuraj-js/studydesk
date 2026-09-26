<?php
// api/admin/feedback.php

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

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        // Search and filter parameters
        $search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
        $status = isset($_GET["status"]) ? trim($_GET["status"]) : "all";
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
            $where[] = "(f.subject LIKE :search OR f.message LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
            $params[":search"] = "%" . $search . "%";
        }

        $validStatuses = ["unread", "read", "resolved"];
        if ($status !== "all" && in_array($status, $validStatuses)) {
            $where[] = "f.status = :status";
            $params[":status"] = $status;
        }

        $validTypes = ["bug_report", "feature_request", "general_feedback", "support"];
        if ($type !== "all" && in_array($type, $validTypes)) {
            $where[] = "f.type = :type";
            $params[":type"] = $type;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Count total feedback matching filters
        $countSql = "SELECT COUNT(*) as total 
                     FROM feedback f 
                     JOIN users u ON f.user_id = u.id 
                     " . $whereClause;
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalFeedback = (int)$stmt->fetch()["total"];

        $totalPages = $totalFeedback > 0 ? (int)ceil($totalFeedback / $perPage) : 1;

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        // Fetch feedback with user info
        $sql = "SELECT f.id, f.user_id, u.username, u.email, f.type, f.subject, f.message, f.status, f.created_at 
                FROM feedback f 
                JOIN users u ON f.user_id = u.id 
                " . $whereClause . " 
                ORDER BY 
                    CASE f.status 
                        WHEN 'unread' THEN 1 
                        WHEN 'read' THEN 2 
                        WHEN 'resolved' THEN 3 
                        ELSE 4 
                    END ASC, 
                    f.created_at DESC 
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $feedback = $stmt->fetchAll();

        // Get counts for filter tabs (always unfiltered)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM feedback");
        $stmt->execute();
        $countAll = (int)$stmt->fetch()["count"];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM feedback WHERE status = 'unread'");
        $stmt->execute();
        $countUnread = (int)$stmt->fetch()["count"];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM feedback WHERE status = 'read'");
        $stmt->execute();
        $countRead = (int)$stmt->fetch()["count"];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM feedback WHERE status = 'resolved'");
        $stmt->execute();
        $countResolved = (int)$stmt->fetch()["count"];

        echo json_encode([
            "success" => true,
            "message" => "Feedback fetched successfully",
            "data" => [
                "feedback" => $feedback,
                "pagination" => [
                    "current_page" => $page,
                    "per_page" => $perPage,
                    "total" => $totalFeedback,
                    "total_pages" => $totalPages
                ],
                "counts" => [
                    "all" => $countAll,
                    "unread" => $countUnread,
                    "read" => $countRead,
                    "resolved" => $countResolved
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
}

if ($method === "PUT") {
    try {
        $feedbackId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

        if ($feedbackId <= 0) {
            echo json_encode(["success" => false, "message" => "Feedback ID required", "data" => null]);
            exit();
        }

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $newStatus = isset($data["status"]) ? trim($data["status"]) : "";

        if (empty($newStatus)) {
            echo json_encode(["success" => false, "message" => "Status is required", "data" => null]);
            exit();
        }

        $validStatuses = ["unread", "read", "resolved"];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(["success" => false, "message" => "Invalid status", "data" => null]);
            exit();
        }

        // Check if feedback exists
        $stmt = $pdo->prepare("SELECT id FROM feedback WHERE id = :id");
        $stmt->execute(["id" => $feedbackId]);
        $feedback = $stmt->fetch();

        if (!$feedback) {
            echo json_encode(["success" => false, "message" => "Feedback not found", "data" => null]);
            exit();
        }

        // Update status
        $stmt = $pdo->prepare("UPDATE feedback SET status = :status WHERE id = :id");
        $stmt->execute(["status" => $newStatus, "id" => $feedbackId]);

        echo json_encode([
            "success" => true,
            "message" => "Feedback status updated successfully",
            "data" => ["id" => $feedbackId, "status" => $newStatus]
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
}

if ($method === "DELETE") {
    try {
        $feedbackId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

        if ($feedbackId <= 0) {
            echo json_encode(["success" => false, "message" => "Feedback ID required", "data" => null]);
            exit();
        }

        // Check if feedback exists
        $stmt = $pdo->prepare("SELECT id FROM feedback WHERE id = :id");
        $stmt->execute(["id" => $feedbackId]);
        $feedback = $stmt->fetch();

        if (!$feedback) {
            echo json_encode(["success" => false, "message" => "Feedback not found", "data" => null]);
            exit();
        }

        // Delete feedback
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = :id");
        $stmt->execute(["id" => $feedbackId]);

        echo json_encode([
            "success" => true,
            "message" => "Feedback deleted successfully",
            "data" => null
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
}

echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
exit();
?>