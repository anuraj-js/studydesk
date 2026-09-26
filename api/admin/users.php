<?php
// api/admin/users.php

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

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        // Search and filter parameters
        $search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
        $role = isset($_GET["role"]) ? trim($_GET["role"]) : "all";
        $level = isset($_GET["level"]) ? trim($_GET["level"]) : "all";
        $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $perPage = 20;

        if ($page < 1) {
            $page = 1;
        }

        // Build WHERE clause
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(username LIKE :search OR email LIKE :search)";
            $params[":search"] = "%" . $search . "%";
        }

        $validRoles = ["user", "admin"];
        if ($role !== "all" && in_array($role, $validRoles)) {
            $where[] = "role = :role";
            $params[":role"] = $role;
        }

        $validLevels = ["school", "plus_two", "bachelor", "master", "others"];
        if ($level !== "all" && in_array($level, $validLevels)) {
            $where[] = "academic_level = :level";
            $params[":level"] = $level;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Count total users
        $countSql = "SELECT COUNT(*) as total FROM users " . $whereClause;
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalUsers = (int)$stmt->fetch()["total"];

        $totalPages = $totalUsers > 0 ? (int)ceil($totalUsers / $perPage) : 1;

        // Ensure page is within bounds
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        // Fetch users
        $sql = "SELECT id, username, email, role, academic_level, gender, created_at 
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
        $targetUserId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

        if ($targetUserId <= 0) {
            echo json_encode(["success" => false, "message" => "User ID required", "data" => null]);
            exit();
        }

        // Prevent admin from deleting themselves
        if ($targetUserId === $userId) {
            echo json_encode(["success" => false, "message" => "You cannot delete your own account", "data" => null]);
            exit();
        }

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = :id");
        $stmt->execute(["id" => $targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            echo json_encode(["success" => false, "message" => "User not found", "data" => null]);
            exit();
        }

        // Prevent deleting the last admin
        if ($targetUser["role"] === "admin") {
            $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
            $stmt->execute();
            $adminCount = (int)$stmt->fetch()["admin_count"];

            if ($adminCount <= 1) {
                echo json_encode(["success" => false, "message" => "Cannot delete the last admin account", "data" => null]);
                exit();
            }
        }

        // Delete user (cascades to all user data)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(["id" => $targetUserId]);

        echo json_encode([
            "success" => true,
            "message" => "User deleted successfully",
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