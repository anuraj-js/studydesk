<?php
// api/auth/login.php

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    try {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        // Null-safe input handling
        $identifier = isset($data["identifier"]) ? trim($data["identifier"]) : "";
        $password = isset($data["password"]) ? trim($data["password"]) : "";

        // Required fields validation
        if (empty($identifier) || empty($password)) {
            echo json_encode([
                "success" => false,
                "message" => "Please enter your username/email and password.",
                "data" => null
            ]);
            exit();
        }

        // Password length validation
        if (strlen($password) < 6) {
            echo json_encode([
                "success" => false,
                "message" => "Password must be at least 6 characters.",
                "data" => null
            ]);
            exit();
        }

        // Find user by username or email
        $sql = "SELECT * FROM users WHERE username = :identifier OR email = :identifier";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["identifier" => $identifier]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid username/email or password.",
                "data" => null
            ]);
            exit();
        }

        // Verify password
        if (!password_verify($password, $user["password"])) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid username/email or password.",
                "data" => null
            ]);
            exit();
        }

        // Set session variables
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["theme"] = $user["theme"] ?? 'blue';
        $_SESSION["dark_mode"] = (int)($user["dark_mode"] ?? 0);

        // Return success
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "data" => [
                "user_id" => $user["id"],
                "username" => $user["username"],
                "role" => $user["role"],
                "theme" => $user["theme"] ?? 'blue',
                "dark_mode" => (int)($user["dark_mode"] ?? 0)
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
}

// Method not allowed
echo json_encode([
    "success" => false,
    "message" => "Method not allowed.",
    "data" => null
]);
exit();
?>