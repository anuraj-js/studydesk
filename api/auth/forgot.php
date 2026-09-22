<?php
// api/auth/forgot.php

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/mail.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$email = isset($data["email"]) ? trim($data["email"]) : "";

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Please enter your email address.", "data" => null]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address.", "data" => null]);
    exit();
}

try {
    // Check if user exists with this email
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    // For security, don't reveal if email exists or not
    if (!$user) {
        // Email not found path
        usleep(rand(2500000, 4000000)); // 2.5s to 4s
        
        echo json_encode([
            "success" => true,
            "message" => "If an account is registered with this email address, a password reset link has been sent.",
            "data" => null
        ]);
        exit();
    }

    // Delete any existing tokens for this user
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $user["id"]]);

    // Generate new token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Insert token
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
    $stmt->execute([
        "user_id" => $user["id"],
        "token" => $token,
        "expires_at" => $expiresAt
    ]);

    // Build reset link
    $resetLink = "http://localhost/studydesk/forms/reset.html?token=" . $token;

    // Send email
    $emailSent = sendResetEmail($email, $user["username"], $resetLink);

    // Add random delay to match the "email not found" scenario
    usleep(rand(500000, 2500000));

    if ($emailSent) {
        echo json_encode([
            "success" => true,
            "message" => "If an account is registered with this email address, a password reset link has been sent.",
            "data" => null
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to send email. Please try again later.",
            "data" => null
        ]);
    }
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