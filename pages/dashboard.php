<?php
// pages/dashboard.php

require_once(__DIR__ . "/../config/gatekeeper_user.php");
require_once(__DIR__ . "/../config/theme.php");

$theme = $_SESSION['theme'] ?? 'blue';
$darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>" data-dark="<?php echo $darkMode ? 'true' : 'false'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/progress.css">
    <link rel="stylesheet" href="../css/calendar.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="dashboard-content">
            <!-- Greeting + Clock + Date Section -->
            <section class="greeting-section">
                <div class="greeting" id="greeting" data-username="<?php echo htmlspecialchars($_SESSION['username']); ?>"></div>
                <div class="study-message" id="studyMessage"></div>
                <div class="clock" id="clock">00:00:00</div>
                <div class="date" id="date"></div>
                <button class="format-toggle-btn" id="formatToggleBtn">
                    <i class="fas fa-clock"></i> Switch to 24-Hour Format
                </button>
            </section>

            <!-- Quick Actions -->
            <section class="actions-section">
                <a href="task_manager.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Task
                </a>
                <a href="exam_planner.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Exam
                </a>
                <a href="pomodoro.php" class="btn btn-success">
                    <i class="fas fa-play"></i> Start Pomodoro
                </a>
            </section>

            <!-- Progress Tracker -->
            <section class="progress-section">
                <h3><i class="fas fa-chart-line" style="color: var(--color-primary);"></i> Progress</h3>
                <div id="progressTracker"></div>
            </section>

            <!-- Calendar (Full Width) -->
            <section class="calendar-section">
                <h3><i class="fas fa-calendar-alt" style="color: var(--color-primary);"></i> Calendar</h3>
                <div id="calendarContainer"></div>
            </section>

            <!-- Recent Activity (Below Calendar) -->
            <section class="activity-section">
                <h3><i class="fas fa-history" style="color: var(--color-primary);"></i> Recent Activity</h3>
                <div id="recentActivity" class="activity-list"></div>
            </section>
        </main>
    </div>

    <script src="../js/notification.js"></script>
    <script src="../js/timezone.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/progress.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/dashboard.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>