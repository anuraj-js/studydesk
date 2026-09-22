<?php
// api/profile/password.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$currentPassword = isset($data["current_password"]) ? $data["current_password"] : "";
$newPassword = isset($data["new_password"]) ? $data["new_password"] : "";
$confirmPassword = isset($data["confirm_password"]) ? $data["confirm_password"] : "";

// All fields required
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(["success" => false, "message" => "All fields are required", "data" => null]);
    exit();
}

// Verify current password
$sql = "SELECT password FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(["id" => $userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user["password"])) {
    echo json_encode(["success" => false, "message" => "Current password is incorrect", "data" => null]);
    exit();
}

// Validate new password (null-safe)
if (empty($newPassword) || strlen($newPassword) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters", "data" => null]);
    exit();
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(["success" => false, "message" => "Passwords do not match", "data" => null]);
    exit();
}

// Hash and update
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = :password WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(["password" => $hashedPassword, "id" => $userId]);

echo json_encode([
    "success" => true,
    "message" => "Password changed successfully",
    "data" => null
]);
exit();
?>