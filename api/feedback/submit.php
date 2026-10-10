<?php
// api/feedback/submit.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/validator.php");

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

// Null-safe input handling
$type = isset($data["type"]) ? trim($data["type"]) : "";
$subject = isset($data["subject"]) ? trim($data["subject"]) : "";
$message = isset($data["message"]) ? trim($data["message"]) : "";

// Validate type
if (isEmpty($type)) {
    echo json_encode(["success" => false, "message" => "Feedback type is required", "data" => null]);
    exit();
}

$validTypes = ["bug_report", "feature_request", "general_feedback", "support"];
if (!in_array($type, $validTypes)) {
    echo json_encode(["success" => false, "message" => "Invalid feedback type", "data" => null]);
    exit();
}

// Validate subject
if (isEmpty($subject)) {
    echo json_encode(["success" => false, "message" => "Subject is required", "data" => null]);
    exit();
}

if (!hasLetters($subject)) {
    echo json_encode(["success" => false, "message" => "Subject must contain at least one letter", "data" => null]);
    exit();
}

if (!isWithinLength($subject, 255)) {
    echo json_encode(["success" => false, "message" => "Subject must be 255 characters or less", "data" => null]);
    exit();
}

// Validate message
if (isEmpty($message)) {
    echo json_encode(["success" => false, "message" => "Message is required", "data" => null]);
    exit();
}

if (!hasLetters($message)) {
    echo json_encode(["success" => false, "message" => "Message must contain at least one letter", "data" => null]);
    exit();
}

if (strlen($message) < 10) {
    echo json_encode(["success" => false, "message" => "Message must be at least 10 characters", "data" => null]);
    exit();
}

if (!isWithinLength($message, 1000)) {
    echo json_encode(["success" => false, "message" => "Message cannot exceed 1000 characters", "data" => null]);
    exit();
}

// --- Spam Prevention + Database Operations (all inside try/catch) ---
try {
    // 1. Duplicate Detection (exact match, 5 minutes — same as cooldown)
    $sql = "SELECT COUNT(*) as count FROM feedback 
            WHERE user_id = :user_id 
            AND subject = :subject 
            AND message = :message 
            AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "user_id" => $userId,
        "subject" => $subject,
        "message" => $message
    ]);
    $duplicate = $stmt->fetch();

    if ($duplicate["count"] > 0) {
        echo json_encode([
            "success" => false,
            "message" => "You have already submitted this feedback recently.",
            "data" => null
        ]);
        exit();
    }

    // 2. Rate Limiting (3 per hour)
    $sql = "SELECT COUNT(*) as count FROM feedback 
            WHERE user_id = :user_id 
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["user_id" => $userId]);
    $rateLimit = $stmt->fetch();

    if ($rateLimit["count"] >= 3) {
        echo json_encode([
            "success" => false,
            "message" => "You have reached the limit of 3 feedback submissions per hour. Please try again later.",
            "data" => null
        ]);
        exit();
    }

    // 3. Time-based Cooldown (5 minutes between submissions)
    $sql = "SELECT 
                TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_since_last
            FROM feedback 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["user_id" => $userId]);
    $lastSubmission = $stmt->fetch();

    if ($lastSubmission) {
        $secondsSinceLast = (int)$lastSubmission["seconds_since_last"];
        
        if ($secondsSinceLast < 300) { // 5 minutes = 300 seconds
            $remaining = 300 - $secondsSinceLast;
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60;
            $timeMessage = $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
            echo json_encode([
                "success" => false,
                "message" => "Please wait {$timeMessage} between submissions.",
                "data" => null
            ]);
            exit();
        }
    }

    // 4. Insert feedback
    $sql = "INSERT INTO feedback (user_id, type, subject, message, status) 
            VALUES (:user_id, :type, :subject, :message, 'unread')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "user_id" => $userId,
        "type" => $type,
        "subject" => $subject,
        "message" => $message
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Feedback submitted successfully",
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