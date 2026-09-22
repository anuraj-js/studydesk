<?php
// pages/pomodoro.php

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
    <title>Pomodoro Timer - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pomodoro.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <main class="pomodoro-container" id="pomodoroContainer">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h1>Pomodoro Timer</h1>
                    <p class="page-subtitle">Focus on your tasks, one session at a time</p>
                </div>
            </div>

            <!-- Modes -->
            <div class="mode-container" id="modeContainer">
                <button class="mode-btn active" data-mode="pomodoro">
                    <i class="fas fa-circle"></i> Pomodoro
                </button>
                <button class="mode-btn" data-mode="short-break">
                    <i class="fas fa-coffee"></i> Short Break
                </button>
                <button class="mode-btn" data-mode="long-break">
                    <i class="fas fa-mug-hot"></i> Long Break
                </button>
            </div>

            <!-- Timer Display -->
            <div class="timer-display-wrapper">
                <div class="timer-display-card" id="timerCard">
                    <div class="timer-inputs">
                        <input type="number" class="timer-input" id="mins-input" min="0" max="99" placeholder="25">
                        <span class="colon">:</span>
                        <input type="number" class="timer-input" id="secs-input" min="0" max="59" placeholder="00">
                    </div>
                    <div class="timer-label" id="timerLabel">Ready</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar-wrapper">
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" id="progressBarFill" style="width: 0%;"></div>
                </div>
                <span class="progress-text" id="progressText">0%</span>
            </div>

            <!-- Time Adjustment Buttons -->
            <div class="time-adjustments">
                <button class="time-adjust-btn" data-seconds="30">+0:30</button>
                <button class="time-adjust-btn" data-seconds="60">+1:00</button>
                <button class="time-adjust-btn" data-seconds="300">+5:00</button>
            </div>

            <!-- Controls -->
            <div class="controls">
                <button class="control-btn play-btn" id="playBtn">
                    <i class="fas fa-play"></i> Start
                </button>
                <button class="control-btn pause-btn" id="pauseBtn" disabled>
                    <i class="fas fa-pause"></i> Pause
                </button>
                <button class="control-btn reset-btn" id="resetBtn">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <!-- Task Section -->
            <div class="task-section">
                <label for="taskName">
                    <i class="fas fa-tag"></i> What are you working on?
                </label>
                <input type="text" id="taskName" placeholder="e.g. Chapter 4 Notes">

                <!-- Proceed without task name checkbox -->
                <div class="task-option">
                    <label class="checkbox-label">
                        <input type="checkbox" id="skipTaskNameConfirm">
                        <span class="checkmark"></span>
                        Proceed without task name
                    </label>
                </div>
            </div>

            <!-- Status -->
            <div class="status">
                <span class="dot" id="statusDot"></span>
                <span id="statusText">Ready</span>
            </div>
        </main>
    </div>

    <!-- Timer Up Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h2 class="modal-title">Time's Up!</h2>
            <p class="modal-message" id="modalMessage">Your session is complete. Great job!</p>
            <button class="modal-btn" id="modalBtn">
                <i class="fas fa-volume-off"></i> Stop Sound
            </button>
        </div>
    </div>

    <script src="../js/notification.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/pomodoro.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>