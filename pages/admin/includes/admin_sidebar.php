<?php
// pages/admin/includes/admin_sidebar.php

require_once(__DIR__ . "/../../../config/gatekeeper_admin.php");

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
        <i class="fas fa-times"></i>
    </button>

    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="sidebar-user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@studydesk.com'); ?></div>
        <span class="sidebar-role-badge">Administrator</span>
    </div>

    <nav class="sidebar-nav">
        <!-- ADMIN -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Admin</div>
            <a href="admin_dashboard.php" class="sidebar-nav-link <?php echo $currentPage == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="admin_users.php" class="sidebar-nav-link <?php echo $currentPage == 'admin_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="admin_feedback.php" class="sidebar-nav-link <?php echo $currentPage == 'admin_feedback.php' ? 'active' : ''; ?>">
                <i class="fas fa-comment"></i> Feedback
            </a>
            <a href="admin_activity.php" class="sidebar-nav-link <?php echo $currentPage == 'admin_activity.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Activity Log
            </a>
        </div>

        <!-- APPEARANCE -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Appearance</div>

            <!-- Themes Toggle -->
            <button class="sidebar-toggle-btn" id="toggleThemes">
                <span>
                    <i class="fas fa-palette"></i> Themes
                </span>
                <i class="fas fa-chevron-down"></i>
            </button>

            <!-- Theme Options -->
            <div class="theme-options-wrapper" id="themeOptionsWrapper" style="display: none;">
                <div class="theme-options">
                    <button class="theme-btn <?php echo ($_SESSION['theme'] ?? 'blue') === 'blue' ? 'active' : ''; ?>" data-theme="blue">
                        <span class="theme-dot blue"></span> Blue
                    </button>
                    <button class="theme-btn <?php echo ($_SESSION['theme'] ?? 'blue') === 'purple' ? 'active' : ''; ?>" data-theme="purple">
                        <span class="theme-dot purple"></span> Purple
                    </button>
                    <button class="theme-btn <?php echo ($_SESSION['theme'] ?? 'blue') === 'red' ? 'active' : ''; ?>" data-theme="red">
                        <span class="theme-dot red"></span> Red
                    </button>
                </div>

                <!-- Dark Mode Toggle -->
                <div class="dark-toggle">
                    <label class="toggle-label">
                        <i class="fas fa-moon"></i> Dark Mode
                        <input type="checkbox" id="darkToggle" <?php echo (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ACCOUNT -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Account</div>
            <a href="../../api/auth/logout.php" class="sidebar-nav-link logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</aside>

<!-- Scroll to Top Button -->
<a href="#" class="scrollup" id="scrollUpBtn" aria-label="Scroll to top">
    <i class="fas fa-arrow-up"></i>
</a>