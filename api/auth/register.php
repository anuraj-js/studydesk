<?php
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(null, 'Invalid request method.', false, 400);
}

$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response(null, 'Invalid CSRF token.', false, 403);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    json_response(null, 'Invalid JSON payload.', false, 400);
}

$fullName = validate_max_length($input['full_name'] ?? '', 100, 'Full Name');
$username = validate_max_length($input['username'] ?? '', 50, 'Username');
$email = validate_max_length($input['email'] ?? '', 150, 'Email');
$password = $input['password'] ?? '';

if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
    json_response(null, 'Username must be 3-50 characters long and contain only letters, numbers, and underscores.', false, 400);
}

$email = validate_email($email, 'Email');
$academicLevel = validate_enum($input['academic_level'] ?? '', ['school', 'plus_two', 'bachelor', 'master', 'others'], 'Academic Level');
$gender = validate_enum($input['gender'] ?? '', ['male', 'female', 'other'], 'Gender');

$recaptchaToken = $input['recaptchaToken'] ?? '';

$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,64}$/';
if (!preg_match($passwordRegex, $password)) {
    json_response(null, 'Password must be 8-64 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.', false, 400);
}

$env = parse_ini_file(__DIR__ . '/../../.env');
$secretKey = $env['RECAPTCHA_SECRET_KEY'] ?? '';

if ($secretKey) {
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $postData = http_build_query([
        'secret' => $secretKey,
        'response' => $recaptchaToken
    ]);
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($verifyUrl, false, $context);
    $responseKeys = json_decode($result, true);

    if (!$responseKeys || !$responseKeys['success']) {
        json_response(null, 'CAPTCHA verification failed. Please try again.', false, 400);
    }
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        json_response(null, 'Username or email is already registered.', false, 400);
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Removed date_of_birth from INSERT
    $insertStmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, academic_level, gender)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $insertStmt->execute([
        $username,
        $email,
        $hashedPassword,
        $fullName,
        $academicLevel,
        $gender
    ]);

    $userId = $pdo->lastInsertId();
    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'user';
    $_SESSION['username'] = $username;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    json_response(null, 'Account created successfully. Welcome to StudyDesk!', true, 200);

} catch (Exception $e) {
    json_error($e);
}