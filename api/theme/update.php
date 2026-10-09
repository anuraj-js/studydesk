<?php
// api/theme/update.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

// Allow both users and admins
$isUser  = !empty($_SESSION["user_id"]);
$isAdmin = !empty($_SESSION["admin_id"]);

if (!$isUser && !$isAdmin) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

try {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    $theme    = isset($data["theme"])     ? trim($data["theme"]) : "blue";
    $darkMode = isset($data["dark_mode"]) ? (int)$data["dark_mode"] : 0;

    $allowedThemes = ["blue", "purple", "red"];
    if (!in_array($theme, $allowedThemes)) {
        echo json_encode(["success" => false, "message" => "Invalid theme selected.", "data" => null]);
        exit();
    }

    // Admins — update DB and session
    if ($isAdmin) {
        $stmt = $pdo->prepare("UPDATE admins SET theme = :theme, dark_mode = :dark_mode WHERE id = :admin_id");
        $stmt->execute(["theme" => $theme, "dark_mode" => $darkMode, "admin_id" => $_SESSION["admin_id"]]);

        $_SESSION["theme"]     = $theme;
        $_SESSION["dark_mode"] = $darkMode;

        echo json_encode(["success" => true, "message" => "Theme updated successfully", "data" => ["theme" => $theme, "dark_mode" => $darkMode]]);
        exit();
    }

    // Users — update DB and session
    $stmt = $pdo->prepare("UPDATE users SET theme = :theme, dark_mode = :dark_mode WHERE id = :user_id");
    $stmt->execute(["theme" => $theme, "dark_mode" => $darkMode, "user_id" => $_SESSION["user_id"]]);

    $_SESSION["theme"]     = $theme;
    $_SESSION["dark_mode"] = $darkMode;

    echo json_encode(["success" => true, "message" => "Theme updated successfully", "data" => ["theme" => $theme, "dark_mode" => $darkMode]]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>