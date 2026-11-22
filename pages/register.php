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
    <title>Register · StudyDesk</title>
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
        
        <h1>Create Account</h1>
        <p class="auth-subtitle">Start your academic journey</p>

        <form id="registerForm">
            <div class="input-group">
                <input type="text" id="fullName" name="full_name" placeholder=" " required autocomplete="name">
                <label><i class="fa-solid fa-signature"></i> Full Name</label>
            </div>

            <div class="input-group">
                <input type="text" id="username" name="username" placeholder=" " required autocomplete="username">
                <label><i class="fa-solid fa-user"></i> Username</label>
            </div>

            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " required autocomplete="email">
                <label><i class="fa-solid fa-envelope"></i> Email</label>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required autocomplete="new-password">
                <label><i class="fa-solid fa-lock"></i> Password</label>
                <button type="button" class="toggle-password" data-target="password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="input-group">
                <input type="password" id="confirmPassword" name="confirm_password" placeholder=" " required autocomplete="new-password">
                <label><i class="fa-solid fa-check-circle"></i> Confirm Password</label>
                <button type="button" class="toggle-password" data-target="confirmPassword">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="select-group">
                <select id="academicLevel" name="academic_level" required>
                    <option value="" disabled selected>-- Select Level --</option>
                    <option value="school">School</option>
                    <option value="plus_two">Plus Two</option>
                    <option value="bachelor">Bachelor</option>
                    <option value="master">Master</option>
                    <option value="others">Others</option>
                </select>
                <label><i class="fa-solid fa-graduation-cap"></i> Academic Level</label>
            </div>

            <!-- REMOVED Date of Birth Input -->

            <div class="gender-group">
                <label class="group-label"><i class="fa-solid fa-venus-mars"></i> Gender</label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="male" required> Male
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="female"> Female
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="other"> Others
                </label>
            </div>

            <div style="margin-top: 1.5rem;"></div>

            <div class="auth-captcha-wrap">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>

            <button type="submit" class="auth-btn" id="registerBtn">
                Create Account
            </button>
        </form>

        <div class="auth-divider">
            Already have an account? <a href="login.php">Login <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <script src="../js/utils.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/dialog.js"></script>
    <script src="../js/api.js"></script>
    <script src="../js/validate.js"></script>
    <script src="../js/register.js"></script>
</body>
</html>