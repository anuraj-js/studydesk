<?php
// api/quick_note/save.php

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

// Null-safe input handling with trim
$content = isset($data["content"]) ? $data["content"] : "";

try {
    // Check if note exists
    $stmt = $pdo->prepare("SELECT id FROM quick_notes WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $userId]);
    $note = $stmt->fetch();

    if ($note) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE quick_notes SET content = :content, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id");
    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO quick_notes (user_id, content) VALUES (:user_id, :content)");
    }

    $stmt->execute(["user_id" => $userId, "content" => $content]);

    // Fetch saved note
    $stmt = $pdo->prepare("SELECT content, updated_at FROM quick_notes WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $userId]);
    $saved = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Note saved successfully",
        "data" => [
            "content" => $saved["content"],
            "updated_at" => $saved["updated_at"]
        ]
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>