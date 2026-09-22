<?php
// api/user/timezone.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized",
        "data" => null
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed",
        "data" => null
    ]);
    exit();
}

$body     = json_decode(file_get_contents("php://input"), true);
$timezone = trim($body["timezone"] ?? "");

if (empty($timezone) || !in_array($timezone, timezone_identifiers_list())) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid timezone",
        "data" => null
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE users SET timezone = :timezone WHERE id = :user_id");
    $stmt->execute(["timezone" => $timezone, "user_id" => $_SESSION["user_id"]]);

    echo json_encode([
        "success" => true,
        "message" => "Timezone updated",
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