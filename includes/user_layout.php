<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../config/db.php';

function render_header(string $pageTitle, string $activePage): void {
    require_user_page();
    
    $csrfToken = csrf_generate();
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT username, email, full_name FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $displayName = $user['full_name'] ?: $user['username'];
    $userEmail = $user['email'];

    $active = function($page) use ($activePage) {
        return $page === $activePage ? 'class="active"' : '';
    };
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> · StudyDesk</title>
    
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/variables.css" />
    <link rel="stylesheet" href="../css/layout.css" />
    <link rel="stylesheet" href="../css/components.css" />
    <link rel="stylesheet" href="../css/features.css" />
    <link rel="stylesheet" href="../css/responsive.css" />
    <script src="../js/theme.js"></script>
    <link rel="icon" href="data:,">
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="nav-brand">
            <img src="../assets/images/studydesk_logo.svg" alt="StudyDesk" />
        </a>

        <button class="hamburger" id="hamburgerBtn">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="nav-menu" id="navMenu">
            <div class="nav-links">
                <a href="home.php" <?php echo $active('home'); ?>><i class="fa-solid fa-house"></i> Home</a>
                <a href="tasks.php" <?php echo $active('tasks'); ?>><i class="fa-solid fa-list-check"></i> Task Manager</a>
                <a href="exams.php" <?php echo $active('exams'); ?>><i class="fa-solid fa-book-open"></i> Exam Planner</a>
                <a href="pomodoro.php" <?php echo $active('pomodoro'); ?>><i class="fa-solid fa-clock"></i> Pomodoro</a>
                <a href="notes.php" <?php echo $active('notes'); ?>><i class="fa-solid fa-note-sticky"></i> Notes</a>
                <a href="profile.php" <?php echo $active('profile'); ?>><i class="fa-solid fa-user"></i> Profile</a>
                <a href="help.php" <?php echo $active('help'); ?>><i class="fa-solid fa-circle-question"></i> Help & Support</a>
            </div>
            <div class="nav-right-actions">
                <button id="logoutBtnDesktop" class="btn btn-danger btn-sm" style="margin-right: 0.5rem; padding: 0.4rem 0.65rem;" title="Logout">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
                <button id="themeToggle" class="theme-btn"></button>
            </div>
        </div>
        <div class="nav-backdrop" id="navBackdrop"></div>
    </nav>

    <div class="mobile-sidebar" id="mobileSidebar">
        <button class="sidebar-close-btn" id="sidebarCloseBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="sidebar-profile">
            <div class="sidebar-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="sidebar-name"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="sidebar-email"><?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-heading">WORKSPACE</div>
            <a href="home.php" class="sidebar-link <?php echo $active('home') ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i> Home
            </a>
            <a href="tasks.php" class="sidebar-link <?php echo $active('tasks') ? 'active' : ''; ?>">
                <i class="fa-solid fa-list-check"></i> Task Manager
            </a>
            <a href="exams.php" class="sidebar-link <?php echo $active('exams') ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-open"></i> Exam Planner
            </a>
            <a href="pomodoro.php" class="sidebar-link <?php echo $active('pomodoro') ? 'active' : ''; ?>">
                <i class="fa-solid fa-clock"></i> Pomodoro
            </a>
            <a href="notes.php" class="sidebar-link <?php echo $active('notes') ? 'active' : ''; ?>">
                <i class="fa-solid fa-note-sticky"></i> Notes
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-heading">APPEARANCE</div>
            <div class="sidebar-theme-toggle">
                <span><i class="fa-solid fa-moon"></i> Dark Mode</span>
                <button id="themeToggleMobile" class="theme-btn"></button>
            </div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-heading">ACCOUNT</div>
            <a href="profile.php" class="sidebar-link <?php echo $active('profile') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user"></i> Profile
            </a>
            <a href="help.php" class="sidebar-link <?php echo $active('help') ? 'active' : ''; ?>">
                <i class="fa-solid fa-circle-question"></i> Help & Support
            </a>
            <a href="#" class="sidebar-link" id="logoutBtnMobile" style="color: var(--danger); margin-top: 0.5rem;">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
<?php
}

function render_footer(string|array $pageScripts = []): void {
?>
    </div>

    <script>window.APP_ROOT = '../';</script>

    <script src="../js/utils.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/dialog.js"></script>
    <script src="../js/api.js"></script>
    <script src="../js/validate.js"></script>
    <script src="../js/nav.js"></script>
    
    <?php 
    $scripts = is_array($pageScripts) ? $pageScripts : ($pageScripts ? [$pageScripts] : []);
    foreach ($scripts as $script) {
        if ($script) {
            echo '<script src="../js/' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
        }
    }
    ?>
</body>
</html>
<?php
}