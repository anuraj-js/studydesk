<?php
// api/auth/reset.php

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$token = isset($data["token"]) ? trim($data["token"]) : "";
$password = isset($data["password"]) ? trim($data["password"]) : "";

if (empty($token)) {
    echo json_encode(["success" => false, "message" => "Invalid reset token.", "data" => null]);
    exit();
}

if (empty($password)) {
    echo json_encode(["success" => false, "message" => "Please enter a new password.", "data" => null]);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters.", "data" => null]);
    exit();
}

try {
    // Find token
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = :token");
    $stmt->execute(["token" => $token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        echo json_encode(["success" => false, "message" => "Invalid or expired reset token.", "data" => null]);
        exit();
    }

    // Check if token is expired
    $now = new DateTime();
    $expiresAt = new DateTime($reset["expires_at"]);

    if ($now > $expiresAt) {
        // Delete expired token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute(["token" => $token]);
        echo json_encode(["success" => false, "message" => "This reset link has expired. Please request a new one.", "data" => null]);
        exit();
    }

    $userId = $reset["user_id"];

    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update user's password
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute([
        "password" => $hashedPassword,
        "id" => $userId
    ]);

    // Delete token after use
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
    $stmt->execute(["token" => $token]);

    echo json_encode([
        "success" => true,
        "message" => "Password reset successfully. You can now login with your new password.",
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