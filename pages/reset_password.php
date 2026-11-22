<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] === 'admin') ? BASE_URL . '/pages/admin/home.php' : BASE_URL . '/pages/home.php';
    header('Location: ' . $redirect);
    exit;
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: login.php');
    exit;
}

$csrfToken = csrf_generate();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password · StudyDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/variables.css" />
    <link rel="stylesheet" href="../css/layout.css" />
    <link rel="stylesheet" href="../css/components.css" />
    <link rel="stylesheet" href="../css/auth.css" />
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="icon" href="data:,">
</head>
<body>

    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>

    <div class="auth-card">
        <img src="../assets/images/studydesk_logo.svg" alt="StudyDesk" class="auth-logo">
        
        <h1>Create New Password</h1>
        <p class="auth-subtitle">Enter your new password below</p>

        <form id="resetForm">
            <input type="hidden" id="resetToken" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="input-group">
                <input type="password" id="newPassword" name="new_password" placeholder=" " required autocomplete="new-password">
                <label><i class="fa-solid fa-lock"></i> New Password</label>
                <button type="button" class="toggle-password" data-target="newPassword">
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

            <button type="submit" class="auth-btn" id="resetBtn">
                Reset Password
            </button>
        </form>

        <div class="auth-divider">
            <a href="login.php">Back to Login</a>
        </div>
    </div>

    <script src="../js/utils.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/dialog.js"></script>
    <script src="../js/api.js"></script>
    <script src="../js/validate.js"></script>
    <script src="../js/reset_password.js"></script>
</body>
</html>