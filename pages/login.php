<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    $redirect = ($role === 'admin') ? BASE_URL . '/pages/admin/home.php' : BASE_URL . '/pages/home.php';
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
    <title>Login · StudyDesk</title>
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
        
        <h1>Welcome Back</h1>
        <p class="auth-subtitle">Login to your account</p>

        <form id="loginForm">
            <div class="select-group">
                <select id="role" name="role" required>
                    <option value="" disabled selected>-- Select Role --</option>
                    <option value="user">Student</option>
                    <option value="admin">Admin</option>
                </select>
                <label><i class="fa-solid fa-user-tag"></i> Sign In As</label>
            </div>

            <div class="input-group">
                <input type="text" id="identifier" name="identifier" placeholder=" " required autocomplete="username">
                <label><i class="fa-solid fa-envelope"></i> Username / Email</label>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required autocomplete="current-password">
                <label><i class="fa-solid fa-lock"></i> Password</label>
                <button type="button" class="toggle-password" id="togglePassword">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="auth-captcha-wrap">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>

            <button type="submit" class="auth-btn" id="loginBtn">
                Login
            </button>

            <div class="auth-footer-links">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
        </form>

        <div class="auth-divider">
            New to StudyDesk? <a href="register.php">Create an account <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <script src="../js/utils.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/dialog.js"></script>
    <script src="../js/api.js"></script>
    <script src="../js/login.js"></script>
</body>
</html>