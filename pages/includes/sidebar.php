<!-- pages/includes/sidebar.php -->

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <button class="sidebar-close" id="sidebarClose">
        <i class="fas fa-times"></i>
    </button>

    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="sidebar-user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? 'user@example.com'); ?></div>
    </div>

    <nav class="sidebar-nav">
        <!-- WORKSPACE -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-label">Workspace</div>
            <a href="dashboard.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="task_manager.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'task_manager.php' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Task Manager
            </a>
            <a href="exam_planner.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'exam_planner.php' ? 'active' : ''; ?>">
                <i class="fas fa-pencil-alt"></i> Exam Planner
            </a>
            <a href="pomodoro.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pomodoro.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pomodoro
            </a>
            <a href="quick_note.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'quick_note.php' ? 'active' : ''; ?>">
                <i class="fas fa-sticky-note"></i> Quick Note
            </a>
            <a href="activity.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'activity.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Activity History
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
            <a href="profile.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="feedback.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
                <i class="fas fa-comment"></i> Feedback
            </a>
            <a href="help.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'help.php' ? 'active' : ''; ?>">
                <i class="fas fa-life-ring"></i> Help & Support
            </a>
            <a href="../api/auth/logout.php" class="sidebar-nav-link logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</aside>

<!-- Scroll to Top Button -->
<a href="#" class="scrollup" id="scrollUpBtn">
    <i class="fas fa-arrow-up"></i>
</a>