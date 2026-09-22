<?php
// api/quick_note/fetch.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT content, updated_at FROM quick_notes WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $userId]);
    $note = $stmt->fetch();

    if ($note) {
        echo json_encode([
            "success" => true,
            "message" => "Note fetched successfully",
            "data" => [
                "content" => $note["content"],
                "updated_at" => $note["updated_at"]
            ]
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "No note found",
            "data" => [
                "content" => "",
                "updated_at" => null
            ]
        ]);
    }
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>