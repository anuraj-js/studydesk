<?php
// api/auth/register.php

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    try {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        // Null-safe input handling
        $username = isset($data["username"]) ? trim($data["username"]) : "";
        $email = isset($data["email"]) ? trim($data["email"]) : "";
        $password = isset($data["password"]) ? trim($data["password"]) : "";
        $academic_level = isset($data["academic_level"]) ? trim($data["academic_level"]) : "";
        $dob = isset($data["dob"]) ? trim($data["dob"]) : "";
        $gender = isset($data["gender"]) ? trim($data["gender"]) : "";
        $phone = isset($data["phone"]) ? (trim($data["phone"]) ?: null) : null;
        $address = isset($data["address"]) ? (trim($data["address"]) ?: null) : null;

        // Required fields validation
        if (empty($username) || empty($email) || empty($password) || empty($academic_level) || empty($dob) || empty($gender)) {
            echo json_encode([
                "success" => false,
                "message" => "Please fill in all required fields.",
                "data" => null
            ]);
            exit();
        }

        // Username length validation
        if (strlen($username) < 3) {
            echo json_encode([
                "success" => false,
                "message" => "Username must be at least 3 characters.",
                "data" => null
            ]);
            exit();
        }

        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false,
                "message" => "Please enter a valid email address.",
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

        // Academic level validation
        $allowedLevels = ["school", "plus_two", "bachelor", "master", "others"];
        if (!in_array($academic_level, $allowedLevels)) {
            echo json_encode([
                "success" => false,
                "message" => "Please select a valid academic level.",
                "data" => null
            ]);
            exit();
        }

        // Gender validation
        $allowedGenders = ["male", "female", "others"];
        if (!in_array($gender, $allowedGenders)) {
            echo json_encode([
                "success" => false,
                "message" => "Please select a valid gender.",
                "data" => null
            ]);
            exit();
        }

        // DOB validation (13+ years)
        $dobDate = DateTime::createFromFormat("Y-m-d", $dob);
        if (!$dobDate || $dobDate->format("Y-m-d") !== $dob) {
            echo json_encode([
                "success" => false,
                "message" => "Please enter a valid date of birth.",
                "data" => null
            ]);
            exit();
        }

        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        if ($age < 13) {
            echo json_encode([
                "success" => false,
                "message" => "You must be at least 13 years old to register.",
                "data" => null
            ]);
            exit();
        }

        // Check if username or email already exists
        $sql = "SELECT username, email FROM users WHERE username = :username OR email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "username" => $username,
            "email" => $email
        ]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            if ($existingUser["username"] === $username) {
                echo json_encode([
                    "success" => false,
                    "message" => "The username is already taken. Please choose a different username.",
                    "data" => null
                ]);
            } elseif ($existingUser["email"] === $email) {
                echo json_encode([
                    "success" => false,
                    "message" => "The email address is already registered. Please use a different email or log in.",
                    "data" => null
                ]);
            }
            exit();
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $sql = "INSERT INTO users (username, email, password, role, academic_level, dob, gender, phone, address) 
                VALUES (:username, :email, :password, 'user', :academic_level, :dob, :gender, :phone, :address)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "username" => $username,
            "email" => $email,
            "password" => $hashedPassword,
            "academic_level" => $academic_level,
            "dob" => $dob,
            "gender" => $gender,
            "phone" => $phone,
            "address" => $address
        ]);

        $userId = $pdo->lastInsertId();

        // Set session variables
        $_SESSION["user_id"] = $userId;
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["role"] = "user";
        $_SESSION["theme"] = "blue";
        $_SESSION["dark_mode"] = false;

        // Return success
        echo json_encode([
            "success" => true,
            "message" => "Registration successful",
            "data" => [
                "user_id" => $userId,
                "username" => $username,
                "role" => "user",
                "theme" => "blue",
                "dark_mode" => false
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