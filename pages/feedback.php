<?php
// pages/feedback.php

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
    <title>Feedback - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/feedback.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <main class="feedback-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div>
                    <h1>Feedback</h1>
                    <p class="page-subtitle">Help us improve StudyDesk</p>
                </div>
            </div>

            <div class="feedback-container">
                <form id="feedbackForm" novalidate>
                    <!-- Type -->
                    <div class="feedback-field">
                        <label for="feedbackType">Type</label>
                        <select id="feedbackType" required>
                            <option value="general_feedback">General Feedback</option>
                            <option value="bug_report">Bug Report</option>
                            <option value="feature_request">Feature Request</option>
                            <option value="support">Support</option>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="feedback-field">
                        <label for="feedbackSubject">Subject</label>
                        <input type="text" id="feedbackSubject" placeholder="Brief summary of your feedback" required maxlength="255">
                    </div>

                    <!-- Message -->
                    <div class="feedback-field feedback-field-textarea">
                        <label for="feedbackMessage">Message</label>
                        <!-- REMOVED: maxlength="1000" — validation is handled by JS + PHP -->
                        <textarea id="feedbackMessage" placeholder="Describe your feedback in detail..." required rows="5"></textarea>
                        <span class="char-counter" id="charCounter"><span id="charCount">0</span> / 1000 characters</span>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitFeedbackBtn">
                        <span id="submitBtnText"><i class="fas fa-paper-plane"></i> Send Feedback</span>
                        <span id="submitBtnSpinner" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Sending...</span>
                    </button>

                    <div id="feedbackSuccess" style="display:none;" class="feedback-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Thank you for your feedback!</span>
                        <span class="feedback-success-sub">We appreciate your input and will review it shortly.</span>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../js/notification.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/feedback.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>