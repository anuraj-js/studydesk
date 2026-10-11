<?php
// api/quick_note/save.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

const MAX_NOTE_LENGTH = 10000;

// --- Auth ---
if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

// --- Method ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];

// --- Input ---
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!is_array($data) || !array_key_exists("content", $data) || !is_string($data["content"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid request body", "data" => null]);
    exit();
}

$content = $data["content"];

// --- Length guard (server-side) ---
if (mb_strlen($content) > MAX_NOTE_LENGTH) {
    http_response_code(413);
    echo json_encode([
        "success" => false,
        "message" => "Note is too long (max " . MAX_NOTE_LENGTH . " characters).",
        "data" => null
    ]);
    exit();
}

try {
    // Upsert — requires a UNIQUE index on quick_notes.user_id
    $stmt = $pdo->prepare(
        "INSERT INTO quick_notes (user_id, content)
         VALUES (:user_id, :content)
         ON DUPLICATE KEY UPDATE
             content = VALUES(content),
             updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([
        "user_id" => $userId,
        "content" => $content
    ]);

    // Fetch the canonical stored values (handles trimming / triggers if any)
    $stmt = $pdo->prepare(
        "SELECT content, updated_at FROM quick_notes WHERE user_id = :user_id"
    );
    $stmt->execute(["user_id" => $userId]);
    $saved = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Note saved successfully",
        "data" => [
            "content"    => $saved["content"] ?? "",
            "updated_at" => $saved["updated_at"] ?? null
        ]
    ]);
    exit();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong. Please try again.",
        "data" => null
    ]);
    exit();
}
?>