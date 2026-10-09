<?php
// config/db.php - Database connection for API files

require_once(__DIR__ . "/database_config.php");
require_once(__DIR__ . "/headers.php"); // Security headers for all API responses

try {
    $pdo = getDbConnection();
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed.",
        "data" => null
    ]);
    exit();
}
?>