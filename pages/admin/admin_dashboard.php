<?php
// pages/admin/admin_dashboard.php

require_once(__DIR__ . "/../../config/gatekeeper_admin.php");
require_once(__DIR__ . "/../../config/theme.php");

$theme = $_SESSION['theme'] ?? 'blue';
$darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>" data-dark="<?php echo $darkMode ? 'true' : 'false'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_dashboard.css">
    <link rel="stylesheet" href="../../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/admin_header.php"); ?>
        <?php include(__DIR__ . "/includes/admin_sidebar.php"); ?>

        <main class="admin-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="page-subtitle">System overview and quick access</p>
                </div>
            </div>

            <!-- Welcome Greeting -->
            <div class="admin-greeting">
                Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number" id="statUsers">...</div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-icon-tasks">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-number" id="statTasks">...</div>
                    <div class="stat-label">Total Tasks</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-icon-exams">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <div class="stat-number" id="statExams">...</div>
                    <div class="stat-label">Total Exams</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-icon-activity">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-number" id="statActivity">...</div>
                    <div class="stat-label">Total Activity</div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="quick-links-card">
                <h3 class="quick-links-title">
                    <i class="fas fa-bolt"></i> Quick Links
                </h3>
                <div class="quick-links-grid">
                    <a href="admin_users.php" class="quick-link">
                        <div class="quick-link-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="quick-link-content">
                            <span class="quick-link-title">Manage Users</span>
                            <span class="quick-link-sub">View, search, delete users</span>
                        </div>
                        <i class="fas fa-arrow-right quick-link-arrow"></i>
                    </a>

                    <a href="admin_feedback.php" class="quick-link">
                        <div class="quick-link-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="quick-link-content">
                            <span class="quick-link-title">View Feedback</span>
                            <span class="quick-link-sub">Manage user feedback</span>
                        </div>
                        <i class="fas fa-arrow-right quick-link-arrow"></i>
                    </a>

                    <a href="admin_activity.php" class="quick-link">
                        <div class="quick-link-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="quick-link-content">
                            <span class="quick-link-title">View Activity</span>
                            <span class="quick-link-sub">Browse all user activity</span>
                        </div>
                        <i class="fas fa-arrow-right quick-link-arrow"></i>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/admin/admin_dashboard.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>