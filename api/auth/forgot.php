<?php
// api/auth/forgot.php

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/mail.php");
require_once(__DIR__ . "/../../config/headers.php");
require_once(__DIR__ . "/../../config/validator.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$email = isset($data["email"]) ? trim($data["email"]) : "";
$ip    = $_SERVER["REMOTE_ADDR"] ?? "unknown";

if (isEmpty($email)) {
    echo json_encode(["success" => false, "message" => "Please enter your email address.", "data" => null]);
    exit();
}

if (!isValidEmail($email)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address.", "data" => null]);
    exit();
}

// Rate limiting — block after 3 attempts in 15 minutes
$window = date("Y-m-d H:i:s", strtotime("-15 minutes"));
$stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE identifier = :identifier AND attempt_type = 'forgot_password' AND attempted_at > :window");
$stmt->execute(["identifier" => $email, "window" => $window]);
$attempts = (int)$stmt->fetchColumn();

if ($attempts >= 3) {
    echo json_encode(["success" => false, "message" => "Too many requests. Please try again in 15 minutes.", "data" => null]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Log attempt even for non-existent emails to prevent enumeration via rate limit bypass
        $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier, attempt_type, ip_address) VALUES (:identifier, 'forgot_password', :ip)");
        $stmt->execute(["identifier" => $email, "ip" => $ip]);

        usleep(rand(2500000, 4000000));
        echo json_encode(["success" => true, "message" => "If an account is registered with this email address, a password reset link has been sent.", "data" => null]);
        exit();
    }

    // Log attempt
    $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier, attempt_type, ip_address) VALUES (:identifier, 'forgot_password', :ip)");
    $stmt->execute(["identifier" => $email, "ip" => $ip]);

    // Delete existing tokens for this user
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :user_id AND role = 'user'");
    $stmt->execute(["user_id" => $user["id"]]);

    // Generate token — store hashed, send raw
    $rawToken    = bin2hex(random_bytes(32));
    $storedToken = hash("sha256", $rawToken);
    $expiresAt   = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, role, token, expires_at) VALUES (:user_id, 'user', :token, :expires_at)");
    $stmt->execute(["user_id" => $user["id"], "token" => $storedToken, "expires_at" => $expiresAt]);

    // Build reset link using APP_URL from .env
    $appUrl    = rtrim(getenv("APP_URL") ?: "http://localhost/studydesk", "/");
    $resetLink = $appUrl . "/forms/reset.html?token=" . $rawToken;

    $emailSent = sendResetEmail($email, $user["username"], $resetLink);

    usleep(rand(500000, 2500000));

    if ($emailSent["success"]) {
        echo json_encode(["success" => true, "message" => "If an account is registered with this email address, a password reset link has been sent.", "data" => null]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send email. Please try again later.", "data" => null]);
    }
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>