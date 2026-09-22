<?php
// pages/quick_note.php

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
    <title>Quick Note - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/quick_note.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <main class="quick-note-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-icon">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <div>
                    <h1>Quick Note</h1>
                    <p class="page-subtitle">Jot down your thoughts, ideas, or quick reminders.</p>
                </div>
            </div>

            <div class="quick-note-container">
                <div class="quick-note-wrapper">
                    <textarea 
                        id="quickNoteTextarea" 
                        class="quick-note-textarea" 
                        placeholder="Write something important..."
                        spellcheck="true"
                    ></textarea>

                    <div class="quick-note-footer">
                        <div class="quick-note-stats">
                            <span class="char-counter" id="charCounter">
                                <i class="fas fa-pencil-alt"></i> <span id="charCount">0</span> chars
                            </span>
                            <span class="word-counter" id="wordCounter">
                                <i class="fas fa-font"></i> <span id="wordCount">0</span> words
                            </span>
                        </div>
                        <div class="quick-note-status" id="noteStatus">
                            <span class="status-dot" id="statusDot"></span>
                            <span class="status-text" id="statusText">
                                <i class="fas fa-circle"></i> Ready
                            </span>
                        </div>
                    </div>

                    <div class="quick-note-actions">
                        <button class="btn btn-danger btn-clear" id="clearNoteBtn">
                            <i class="fas fa-trash"></i> Clear Note
                        </button>
                        <button class="btn btn-primary btn-save" id="saveNoteBtn">
                            <i class="fas fa-save"></i> <span id="saveBtnText">Save Now</span> <span class="keyboard-hint">(Ctrl+S)</span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/notification.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/quick_note.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>