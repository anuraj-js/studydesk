<?php
// api/quick_note/fetch.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

// --- Auth ---
if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

// --- Method ---
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];

try {
    $stmt = $pdo->prepare(
        "SELECT content, updated_at FROM quick_notes WHERE user_id = :user_id"
    );
    $stmt->execute(["user_id" => $userId]);
    $note = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => $note ? "Note fetched successfully" : "No note found",
        "data" => [
            "content"    => $note["content"] ?? "",
            "updated_at" => $note["updated_at"] ?? null
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