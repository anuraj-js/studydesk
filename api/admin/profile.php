<?php
// api/admin/profile.php

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
$method  = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = :id");
        $stmt->execute(["id" => $adminId]);
        $admin = $stmt->fetch();

        if (!$admin) {
            echo json_encode(["success" => false, "message" => "Admin not found", "data" => null]);
            exit();
        }

        echo json_encode(["success" => true, "message" => "Profile fetched successfully", "data" => $admin]);
        exit();

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
        exit();
    }
}

if ($method === "PUT") {
    try {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $fields = [];
        $params = [];

        if (isset($data["username"])) {
            $username = trim($data["username"]);
            if (strlen($username) < 3) {
                echo json_encode(["success" => false, "message" => "Username must be at least 3 characters", "data" => null]);
                exit();
            }
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = :username AND id != :admin_id");
            $stmt->execute(["username" => $username, "admin_id" => $adminId]);
            if ($stmt->fetch()) {
                echo json_encode(["success" => false, "message" => "Username is already taken", "data" => null]);
                exit();
            }
            $fields[] = "username = :username";
            $params[":username"] = $username;
        }

        if (isset($data["email"])) {
            $email = trim($data["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["success" => false, "message" => "Please enter a valid email address", "data" => null]);
                exit();
            }
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = :email AND id != :admin_id");
            $stmt->execute(["email" => $email, "admin_id" => $adminId]);
            if ($stmt->fetch()) {
                echo json_encode(["success" => false, "message" => "Email is already registered", "data" => null]);
                exit();
            }
            $fields[] = "email = :email";
            $params[":email"] = $email;
        }

        if (empty($fields)) {
            echo json_encode(["success" => false, "message" => "No fields to update", "data" => null]);
            exit();
        }

        $params[":admin_id"] = $adminId;
        $sql = "UPDATE admins SET " . implode(", ", $fields) . " WHERE id = :admin_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = :id");
        $stmt->execute(["id" => $adminId]);
        $admin = $stmt->fetch();

        if (isset($data["username"])) $_SESSION["username"] = $username;
        if (isset($data["email"]))    $_SESSION["email"]    = $email;

        echo json_encode(["success" => true, "message" => "Profile updated successfully", "data" => ["user" => $admin]]);
        exit();

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
        exit();
    }
}

echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
exit();
?>