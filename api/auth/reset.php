<?php
// api/auth/reset.php

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/headers.php");
require_once(__DIR__ . "/../../config/validator.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$rawToken = isset($data["token"])    ? trim($data["token"]) : "";
$password = isset($data["password"]) ? $data["password"]    : "";

if (isEmpty($rawToken)) {
    echo json_encode(["success" => false, "message" => "Invalid reset token.", "data" => null]);
    exit();
}

if (isEmpty($password)) {
    echo json_encode(["success" => false, "message" => "Please enter a new password.", "data" => null]);
    exit();
}

if (!isValidPassword($password)) {
    echo json_encode(["success" => false, "message" => "Password must be at least 8 characters and include letters and numbers.", "data" => null]);
    exit();
}

try {
    // Hash the incoming token before querying
    $storedToken = hash("sha256", $rawToken);

    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = :token");
    $stmt->execute(["token" => $storedToken]);
    $reset = $stmt->fetch();

    if (!$reset) {
        echo json_encode(["success" => false, "message" => "Invalid or expired reset token.", "data" => null]);
        exit();
    }

    $now       = new DateTime();
    $expiresAt = new DateTime($reset["expires_at"]);

    if ($now > $expiresAt) {
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute(["token" => $storedToken]);
        echo json_encode(["success" => false, "message" => "This reset link has expired. Please request a new one.", "data" => null]);
        exit();
    }

    $userId         = $reset["user_id"];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute(["password" => $hashedPassword, "id" => $userId]);

    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
    $stmt->execute(["token" => $storedToken]);

    echo json_encode(["success" => true, "message" => "Password reset successfully. You can now login with your new password.", "data" => null]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>