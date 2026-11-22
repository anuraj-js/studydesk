<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] === 'admin') ? BASE_URL . '/pages/admin/home.php' : BASE_URL . '/pages/home.php';
    header('Location: ' . $redirect);
    exit;
}

$csrfToken = csrf_generate();
$env = parse_ini_file(__DIR__ . '/../.env');
$siteKey = $env['RECAPTCHA_SITE_KEY'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password · StudyDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/variables.css" />
    <link rel="stylesheet" href="../css/layout.css" />
    <link rel="stylesheet" href="../css/components.css" />
    <link rel="stylesheet" href="../css/auth.css" />
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="icon" href="data:,">
</head>
<body>

    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>

    <div class="auth-card">
        <img src="../assets/images/studydesk_logo.svg" alt="StudyDesk" class="auth-logo">
        
        <h1>Forgot Password</h1>
        <p class="auth-subtitle">Enter your email to reset your password</p>

        <form id="forgotForm">
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " required autocomplete="email">
                <label><i class="fa-solid fa-envelope"></i> Email Address</label>
            </div>

            <div class="auth-captcha-wrap">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>

            <button type="submit" class="auth-btn" id="forgotBtn">
                Send Reset Link
            </button>
        </form>

        <div class="auth-divider">
            Remember your password? <a href="login.php">Back to Login <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <script src="../js/utils.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/api.js"></script>
    <script src="../js/validate.js"></script>
    <script src="../js/forgot_password.js"></script>
</body>
</html>