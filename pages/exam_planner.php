<?php
// pages/exam_planner.php

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
    <title>Exam Planner - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/exams.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <main class="exam-planner">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-header-icon">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <div>
                        <h1>Exam Planner</h1>
                        <p class="page-subtitle">Plan and track your exam preparation</p>
                    </div>
                </div>
                <button id="openExamPanelBtn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Exam
                </button>
            </div>

            <!-- Search Bar -->
            <div class="exam-search">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="examSearchInput" placeholder="Search exams by name or subject...">
                    <button id="clearExamSearchBtn" class="clear-search" style="display:none;" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div id="examSearchInfo" class="search-info" style="display:none;"></div>
            </div>

            <!-- Exam Controls: Sort + Header -->
            <div class="exam-planner-header">
                <h2>Your Exams</h2>
                <div class="exam-controls">
                    <div class="exam-sort">
                        <label for="sortBy">
                            <i class="fas fa-sort"></i> Sort by:
                        </label>
                        <select id="sortBy">
                            <option value="exam_date_asc" selected>Date (Earliest first)</option>
                            <option value="exam_date_desc">Date (Latest first)</option>
                            <option value="exam_name">Name (A → Z)</option>
                            <option value="subject">Subject (A → Z)</option>
                            <option value="progress">Progress (Most complete)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="examList" class="exam-list"></div>
        </main>
    </div>

    <!-- Exam Panel (Create/Edit) -->
    <div id="examPanel" class="slide-panel">
        <div class="panel-header">
            <h3 id="examPanelTitle">Add New Exam</h3>
            <button class="close-btn" id="closeExamPanelBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="examForm" novalidate>
            <input type="hidden" id="examId" value="">
            <div class="form-group">
                <label for="examName">Exam name</label>
                <input type="text" id="examName" placeholder="e.g. Midterm, Final, Pre-Board" required>
            </div>
            <div class="form-group">
                <label for="examSubject">Subject</label>
                <input type="text" id="examSubject" placeholder="e.g. Physics, Chemistry, Mathematics" required>
            </div>
            <div class="form-group">
                <label for="examDate">Exam date</label>
                <input type="date" id="examDate" required>
            </div>
            <button type="submit" id="examSubmitBtn" class="btn btn-primary">Create exam plan</button>
        </form>
    </div>

    <div id="examPanelOverlay" class="panel-overlay"></div>

    <script src="../js/notification.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/exams.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>