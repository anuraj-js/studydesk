<?php
// api/auth/register.php

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../config/validator.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed.", "data" => null]);
    exit();
}

try {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    $username       = isset($data["username"])       ? trim($data["username"])       : "";
    $email          = isset($data["email"])          ? trim($data["email"])          : "";
    $password       = isset($data["password"])       ? $data["password"]             : "";
    $academic_level = isset($data["academic_level"]) ? trim($data["academic_level"]) : "";
    $dob            = isset($data["dob"])            ? trim($data["dob"])            : "";
    $gender         = isset($data["gender"])         ? trim($data["gender"])         : "";
    $phone          = isset($data["phone"])          ? (trim($data["phone"]) ?: null) : null;
    $address        = isset($data["address"])        ? (trim($data["address"]) ?: null) : null;
    $captcha        = isset($data["g-recaptcha-response"]) ? $data["g-recaptcha-response"] : "";

    // Verify reCAPTCHA first
    $secret = getenv("RECAPTCHA_SECRET_KEY");
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$captcha}");
    $captchaResult = json_decode($verify, true);
    if (!$captchaResult["success"]) {
        echo json_encode(["success" => false, "message" => "CAPTCHA verification failed.", "data" => null]);
        exit();
    }

    // Required fields
    if (isEmpty($username) || isEmpty($email) || isEmpty($password) || isEmpty($academic_level) || isEmpty($dob) || isEmpty($gender)) {
        echo json_encode(["success" => false, "message" => "Please fill in all required fields.", "data" => null]);
        exit();
    }

    // Username: 3–50 chars, letters/numbers/underscores only, cannot start with a digit
    if (!isAlphanumericUsername($username)) {
        echo json_encode(["success" => false, "message" => "Username must be 3–50 characters, contain only letters, numbers, or underscores, and cannot start with a number.", "data" => null]);
        exit();
    }

    // Email format
    if (!isValidEmail($email)) {
        echo json_encode(["success" => false, "message" => "Please enter a valid email address.", "data" => null]);
        exit();
    }

    // Email max length
    if (!isWithinLength($email, 100)) {
        echo json_encode(["success" => false, "message" => "Email must be 100 characters or less.", "data" => null]);
        exit();
    }

    // Password: 8–72, letter + number
    if (!isValidPassword($password)) {
        echo json_encode(["success" => false, "message" => "Password must be 8–72 characters and include letters and numbers.", "data" => null]);
        exit();
    }

    // Academic level
    $allowedLevels = ["school", "plus_two", "bachelor", "master", "others"];
    if (!in_array($academic_level, $allowedLevels)) {
        echo json_encode(["success" => false, "message" => "Please select a valid academic level.", "data" => null]);
        exit();
    }

    // Gender
    $allowedGenders = ["male", "female", "others"];
    if (!in_array($gender, $allowedGenders)) {
        echo json_encode(["success" => false, "message" => "Please select a valid gender.", "data" => null]);
        exit();
    }

    // DOB format and age
    if (!isValidDate($dob)) {
        echo json_encode(["success" => false, "message" => "Please enter a valid date of birth.", "data" => null]);
        exit();
    }

    if (!isAtLeast13($dob)) {
        echo json_encode(["success" => false, "message" => "You must be at least 13 years old to register.", "data" => null]);
        exit();
    }

    // Phone (optional)
    if ($phone !== null && !isValidPhone($phone)) {
        echo json_encode(["success" => false, "message" => "Phone number must be exactly 10 digits.", "data" => null]);
        exit();
    }

    // Address (optional)
    if ($address !== null && !isWithinLength($address, 255)) {
        echo json_encode(["success" => false, "message" => "Address must be 255 characters or less.", "data" => null]);
        exit();
    }

    // Check existing username or email
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email");
    $stmt->execute(["username" => $username, "email" => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing["username"] === $username) {
            echo json_encode(["success" => false, "message" => "The username is already taken. Please choose a different username.", "data" => null]);
        } else {
            echo json_encode(["success" => false, "message" => "The email address is already registered. Please use a different email or log in.", "data" => null]);
        }
        exit();
    }

    // Hash password and insert
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, academic_level, dob, gender, phone, address) VALUES (:username, :email, :password, :academic_level, :dob, :gender, :phone, :address)");
    $stmt->execute([
        "username"       => $username,
        "email"          => $email,
        "password"       => $hashedPassword,
        "academic_level" => $academic_level,
        "dob"            => $dob,
        "gender"         => $gender,
        "phone"          => $phone,
        "address"        => $address
    ]);

    $userId = $pdo->lastInsertId();

    // Regenerate session ID
    session_regenerate_id(true);

    $_SESSION["user_id"]   = $userId;
    $_SESSION["username"]  = $username;
    $_SESSION["email"]     = $email;
    $_SESSION["role"]      = "user";
    $_SESSION["theme"]     = "blue";
    $_SESSION["dark_mode"] = 0;

    echo json_encode([
        "success" => true,
        "message" => "Registration successful",
        "data" => [
            "user_id"  => $userId,
            "username" => $username,
            "role"     => "user",
            "theme"    => "blue",
            "dark_mode" => 0
        ]
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>