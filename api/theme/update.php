<?php
// api/theme/update.php

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

try {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Null-safe input handling
    $theme = isset($data["theme"]) ? trim($data["theme"]) : "blue";
    $darkMode = isset($data["dark_mode"]) ? (int)$data["dark_mode"] : 0;

    // Whitelist validation for theme
    $allowedThemes = ["blue", "purple", "red"];
    if (!in_array($theme, $allowedThemes)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid theme selected.",
            "data" => null
        ]);
        exit();
    }

    $userId = $_SESSION["user_id"];

    $stmt = $pdo->prepare("UPDATE users SET theme = :theme, dark_mode = :dark_mode WHERE id = :user_id");
    $stmt->execute([
        "theme" => $theme,
        "dark_mode" => $darkMode,
        "user_id" => $userId
    ]);

    // Update session - consistent with database (0 or 1)
    $_SESSION["theme"] = $theme;
    $_SESSION["dark_mode"] = $darkMode;

    echo json_encode([
        "success" => true,
        "message" => "Theme updated successfully",
        "data" => [
            "theme" => $theme,
            "dark_mode" => $darkMode
        ]
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