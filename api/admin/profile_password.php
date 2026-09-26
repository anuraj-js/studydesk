<?php
// api/admin/profile_password.php

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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Null-safe input (do NOT trim — spaces are valid password characters)
$currentPassword = isset($data["current_password"]) ? $data["current_password"] : "";
$newPassword     = isset($data["new_password"]) ? $data["new_password"] : "";
$confirmPassword = isset($data["confirm_password"]) ? $data["confirm_password"] : "";

// All fields required
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(["success" => false, "message" => "All fields are required", "data" => null]);
    exit();
}

// Validate new password length
if (strlen($newPassword) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters", "data" => null]);
    exit();
}

// Confirm match
if ($newPassword !== $confirmPassword) {
    echo json_encode(["success" => false, "message" => "Passwords do not match", "data" => null]);
    exit();
}

try {
    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute(["id" => $userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user["password"])) {
        echo json_encode(["success" => false, "message" => "Current password is incorrect", "data" => null]);
        exit();
    }

    // Prevent using same password
    if (password_verify($newPassword, $user["password"])) {
        echo json_encode(["success" => false, "message" => "New password must be different from current password", "data" => null]);
        exit();
    }

    // Hash and update
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute(["password" => $hashedPassword, "id" => $userId]);

    echo json_encode([
        "success" => true,
        "message" => "Password changed successfully",
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
?>