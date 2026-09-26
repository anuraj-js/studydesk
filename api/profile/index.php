<?php
// api/profile/index.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        $sql = "SELECT id, username, email, phone, address, academic_level, dob, gender, theme, created_at FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(["success" => false, "message" => "User not found", "data" => null]);
            exit();
        }

        echo json_encode([
            "success" => true,
            "message" => "Profile fetched successfully",
            "data" => $user
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "PUT") {
    try {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $fields = [];
        $params = [];

        // Username
        if (isset($data["username"])) {
            $username = trim($data["username"]);
            if (strlen($username) < 3) {
                echo json_encode(["success" => false, "message" => "Username must be at least 3 characters", "data" => null]);
                exit();
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :user_id");
            $stmt->execute(["username" => $username, "user_id" => $userId]);
            if ($stmt->fetch()) {
                echo json_encode(["success" => false, "message" => "Username is already taken", "data" => null]);
                exit();
            }
            $fields[] = "username = :username";
            $params[":username"] = $username;
        }

        // Email
        if (isset($data["email"])) {
            $email = trim($data["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["success" => false, "message" => "Please enter a valid email address", "data" => null]);
                exit();
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
            $stmt->execute(["email" => $email, "user_id" => $userId]);
            if ($stmt->fetch()) {
                echo json_encode(["success" => false, "message" => "Email is already registered", "data" => null]);
                exit();
            }
            $fields[] = "email = :email";
            $params[":email"] = $email;
        }

        // Phone
        if (isset($data["phone"])) {
            $fields[] = "phone = :phone";
            $params[":phone"] = trim($data["phone"]) ?: null;
        }

        // Address
        if (isset($data["address"])) {
            $fields[] = "address = :address";
            $params[":address"] = trim($data["address"]) ?: null;
        }

        // Academic Level
        if (isset($data["academic_level"])) {
            $validLevels = ["school", "plus_two", "bachelor", "master", "others"];
            if (!in_array($data["academic_level"], $validLevels)) {
                echo json_encode(["success" => false, "message" => "Invalid academic level", "data" => null]);
                exit();
            }
            $fields[] = "academic_level = :academic_level";
            $params[":academic_level"] = $data["academic_level"];
        }

        // Gender
        if (isset($data["gender"])) {
            $validGenders = ["male", "female", "others"];
            if (!in_array($data["gender"], $validGenders)) {
                echo json_encode(["success" => false, "message" => "Invalid gender", "data" => null]);
                exit();
            }
            $fields[] = "gender = :gender";
            $params[":gender"] = $data["gender"];
        }

        // Date of Birth
        if (isset($data["dob"])) {
            $dob = $data["dob"];
            $dobDate = DateTime::createFromFormat("Y-m-d", $dob);
            if (!$dobDate || $dobDate->format("Y-m-d") !== $dob) {
                echo json_encode(["success" => false, "message" => "Invalid date of birth", "data" => null]);
                exit();
            }
            $today = new DateTime();
            $age = $today->diff($dobDate)->y;
            if ($age < 13) {
                echo json_encode(["success" => false, "message" => "You must be at least 13 years old", "data" => null]);
                exit();
            }
            $fields[] = "dob = :dob";
            $params[":dob"] = $dob;
        }

        if (empty($fields)) {
            echo json_encode(["success" => false, "message" => "No fields to update", "data" => null]);
            exit();
        }

        $params[":user_id"] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Fetch updated profile
        $sql = "SELECT id, username, email, phone, address, academic_level, dob, gender, theme, created_at FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $userId]);
        $user = $stmt->fetch();

        // Update session with trimmed values
        if (isset($data["username"])) {
            $_SESSION["username"] = $username;
        }
        if (isset($data["email"])) {
            $_SESSION["email"] = $email;
        }

        echo json_encode([
            "success" => true,
            "message" => "Profile updated successfully",
            "data" => $user
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
exit();
?>