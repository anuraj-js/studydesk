<?php
// api/auth/login.php

session_set_cookie_params([
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

    $role       = isset($data["role"])       ? trim($data["role"])       : "";
    $identifier = isset($data["identifier"]) ? trim($data["identifier"]) : "";
    $password   = isset($data["password"])   ? $data["password"]         : "";
    $captcha    = isset($data["g-recaptcha-response"]) ? $data["g-recaptcha-response"] : "";

    // Validate role
    if (!in_array($role, ["user", "admin"])) {
        echo json_encode(["success" => false, "message" => "Please select a valid role.", "data" => null]);
        exit();
    }

    // Validate required fields
    if (isEmpty($identifier) || isEmpty($password)) {
        echo json_encode(["success" => false, "message" => "Please enter your username/email and password.", "data" => null]);
        exit();
    }

    // Verify reCAPTCHA
    $secret = getenv("RECAPTCHA_SECRET_KEY");
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$captcha}");
    $captchaResult = json_decode($verify, true);
    if (!$captchaResult["success"]) {
        echo json_encode(["success" => false, "message" => "CAPTCHA verification failed.", "data" => null]);
        exit();
    }

    // Rate limiting — block after 5 failed attempts in 15 minutes
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $window = date("Y-m-d H:i:s", strtotime("-15 minutes"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE identifier = :identifier AND attempt_type = 'login' AND attempted_at > :window");
    $stmt->execute(["identifier" => $identifier, "window" => $window]);
    $attempts = (int)$stmt->fetchColumn();

    if ($attempts >= 5) {
        echo json_encode(["success" => false, "message" => "Too many failed attempts. Please try again in 15 minutes.", "data" => null]);
        exit();
    }

    // Clean up old attempts older than 15 minutes
    $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < :window")->execute(["window" => $window]);

    // Query the correct table based on role
    if ($role === "admin") {
        $sql = "SELECT id, username, email, password FROM admins WHERE username = :identifier OR email = :identifier";
    } else {
        $sql = "SELECT id, username, email, password, theme, dark_mode FROM users WHERE username = :identifier OR email = :identifier";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["identifier" => $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password"])) {
        // Log failed attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier, attempt_type, ip_address) VALUES (:identifier, 'login', :ip)");
        $stmt->execute(["identifier" => $identifier, "ip" => $ip]);

        echo json_encode(["success" => false, "message" => "Invalid username/email or password.", "data" => null]);
        exit();
    }

    // Clear attempts on success
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE identifier = :identifier AND attempt_type = 'login'");
    $stmt->execute(["identifier" => $identifier]);

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    // Set session variables
    if ($role === "admin") {
        $_SESSION["admin_id"]  = $user["id"];
        $_SESSION["username"]  = $user["username"];
        $_SESSION["role"]      = "admin";
        $_SESSION["email"]     = $user["email"];
    } else {
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["username"]  = $user["username"];
        $_SESSION["role"]      = "user";
        $_SESSION["email"]     = $user["email"];
        $_SESSION["theme"]     = $user["theme"] ?? "blue";
        $_SESSION["dark_mode"] = (int)($user["dark_mode"] ?? 0);
    }

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "data" => [
            "role"  => $role,
            "theme" => $user["theme"] ?? "blue",
            "dark_mode" => (int)($user["dark_mode"] ?? 0)
        ]
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>