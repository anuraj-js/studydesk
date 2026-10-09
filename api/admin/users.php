<?php
// api/admin/users.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["admin_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

if (empty($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Forbidden", "data" => null]);
    exit();
}

$adminId = $_SESSION["admin_id"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        $search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
        $level  = isset($_GET["level"])  ? trim($_GET["level"])  : "all";
        $page   = isset($_GET["page"])   ? (int)$_GET["page"]   : 1;
        $perPage = 20;

        if ($page < 1) $page = 1;

        $where  = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(username LIKE :search OR email LIKE :search)";
            $params[":search"] = "%" . $search . "%";
        }

        $validLevels = ["school", "plus_two", "bachelor", "master", "others"];
        if ($level !== "all" && in_array($level, $validLevels)) {
            $where[] = "academic_level = :level";
            $params[":level"] = $level;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $countSql = "SELECT COUNT(*) as total FROM users " . $whereClause;
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalUsers = (int)$stmt->fetch()["total"];

        $totalPages = $totalUsers > 0 ? (int)ceil($totalUsers / $perPage) : 1;
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT id, username, email, academic_level, gender, created_at 
                FROM users 
                " . $whereClause . " 
                ORDER BY created_at DESC 
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        echo json_encode([
            "success" => true,
            "message" => "Users fetched successfully",
            "data" => [
                "users" => $users,
                "pagination" => [
                    "current_page" => $page,
                    "per_page" => $perPage,
                    "total" => $totalUsers,
                    "total_pages" => $totalPages
                ]
            ]
        ]);
        exit();

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
        exit();
    }
}

if ($method === "DELETE") {
    try {
        $targetUserId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

        if ($targetUserId <= 0) {
            echo json_encode(["success" => false, "message" => "User ID required", "data" => null]);
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = :id");
        $stmt->execute(["id" => $targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            echo json_encode(["success" => false, "message" => "User not found", "data" => null]);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(["id" => $targetUserId]);

        echo json_encode(["success" => true, "message" => "User deleted successfully", "data" => null]);
        exit();

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
        exit();
    }
}

echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
exit();
?>