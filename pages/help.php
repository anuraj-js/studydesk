<?php
// pages/help.php

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
    <title>Help & Support - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/help.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <main class="help-page">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-icon">
                    <i class="fas fa-life-ring"></i>
                </div>
                <div>
                    <h1>Help & Support</h1>
                    <p class="page-subtitle">Learn how to use StudyDesk effectively</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="help-search">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="helpSearchInput" placeholder="Search help topics...">
                    <button id="clearSearchBtn" class="clear-search" style="display:none;" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div id="searchInfo" class="search-info" style="display:none;"></div>
            </div>

            <!-- Help Sections -->
            <div class="help-container" id="helpContainer">

               <!-- 1. Dashboard -->
                <div class="help-section" data-title="dashboard" data-keywords="stats, quick actions, tasks, exams, activity, progress, calendar, streak">
                    <button class="help-toggle" data-target="help-dashboard">
                        <span><i class="fas fa-chart-pie"></i> Dashboard</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-dashboard">
                        <p><strong>Overview:</strong> The Dashboard gives you a complete overview of your StudyDesk activity, progress, and upcoming events.</p>
                        <ul>
                            <li><strong>Greeting & Clock</strong> — Personalized greeting with current time and date (12/24-hour toggle)</li>
                            <li><strong>Quick Actions</strong> — One-click buttons to create a new Task, Exam, or start a Pomodoro session</li>
                            <li><strong>Progress Tracker</strong> — View your stats (Tasks Completed, Exams Created, Topics Completed, Exams Completed, Pomodoro Sessions), Streak (with longest streak history), and Most Productive Day</li>
                            <li><strong>Calendar</strong> — Monthly view showing all your tasks and exams. Click any date to see details. Navigate with Previous/Next/Today buttons.</li>
                            <li><strong>Recent Activity</strong> — Track your latest actions from the last 90 days</li>
                        </ul>
                        <div class="help-rules">
                            <p><strong><i class="fas fa-fire"></i> Streak Rules:</strong></p>
                            <ul>
                                <li>Streak counts consecutive days with at least 1 activity</li>
                                <li>Activities include: completing a task, creating an exam, completing a topic, completing an exam, or finishing a Pomodoro session</li>
                                <li>Streak breaks if you have no activity on any day</li>
                                <li>Longest streak shows your personal best</li>
                                <li>Streak resets to 0 if you miss a day</li>
                                <li>Last 90 days of activity are tracked</li>
                            </ul>
                        </div>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-none">None</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Task Manager -->
                <div class="help-section" data-title="task manager tasks" data-keywords="create, edit, complete, delete, search, sort, tabs, priority, due date">
                    <button class="help-toggle" data-target="help-tasks">
                        <span><i class="fas fa-tasks"></i> Task Manager</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-tasks">
                        <p><strong>Overview:</strong> Organize and track all your tasks in one place.</p>
                        <ul>
                            <li><strong>Create Tasks</strong> — Add tasks with a title, type (Assignment, Study, Coding, Others), priority (Low, Medium, High), due date, and optional description</li>
                            <li><strong>Task Tabs</strong> — View tasks by category: Today (due today), Upcoming (due in the future), Overdue (past due date)</li>
                            <li><strong>Search</strong> — Find tasks by name or description with real-time filtering</li>
                            <li><strong>Sort</strong> — Sort tasks by Priority, Due Date (earliest/latest), or Type</li>
                            <li><strong>Complete Tasks</strong> — Mark a task as complete (it will be removed and logged in activity history)</li>
                            <li><strong>Edit Tasks</strong> — Update any task details</li>
                            <li><strong>Delete Tasks</strong> — Remove tasks you no longer need</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Clear search
                        </div>
                    </div>
                </div>

                <!-- 3. Exam Planner -->
                <div class="help-section" data-title="exam planner exams topics" data-keywords="create, add topics, progress, countdown, complete, search, sort">
                    <button class="help-toggle" data-target="help-exams">
                        <span><i class="fas fa-pencil-alt"></i> Exam Planner</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-exams">
                        <p><strong>Overview:</strong> Plan and track your exam preparation effectively.</p>
                        <ul>
                            <li><strong>Create Exams</strong> — Add an exam with name, subject, and date</li>
                            <li><strong>Add Topics</strong> — Break down your exam into manageable topics</li>
                            <li><strong>Progress Tracking</strong> — See completion percentage as you finish topics</li>
                            <li><strong>Complete Topics</strong> — Mark topics as complete (they are removed from the list)</li>
                            <li><strong>Complete Exam</strong> — When all topics are done, mark the exam as complete (it will be archived)</li>
                            <li><strong>Countdown Timer</strong> — See the time remaining until each exam</li>
                            <li><strong>Search</strong> — Find exams by name or subject</li>
                            <li><strong>Sort</strong> — Sort exams by Date, Name, Subject, or Progress</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Clear search
                        </div>
                    </div>
                </div>

                <!-- 4. Pomodoro Timer -->
                <div class="help-section" data-title="pomodoro timer focus break" data-keywords="modes, start, pause, reset, task label, sound, logging">
                    <button class="help-toggle" data-target="help-pomodoro">
                        <span><i class="fas fa-clock"></i> Pomodoro Timer</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-pomodoro">
                        <p><strong>Overview:</strong> Stay focused using the Pomodoro technique — study in timed sessions with breaks.</p>
                        <ul>
                            <li><strong>Three Modes</strong> — Choose between Pomodoro (focus), Short Break, and Long Break</li>
                            <li><strong>Custom Timer</strong> — Manually adjust the time for each session</li>
                            <li><strong>Start / Pause / Reset</strong> — Full control over your timer</li>
                            <li><strong>Task Label</strong> — Name what you're working on (sessions are logged in activity history)</li>
                            <li><strong>Sound Alert</strong> — Timer rings when your session is complete</li>
                            <li><strong>Session Logging</strong> — Only completed Pomodoro sessions are saved to your activity history. Short breaks and long breaks are not logged.</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Space</span> Start / Pause
                            <span class="shortcut-key">Escape</span> Reset
                        </div>
                    </div>
                </div>

                <!-- 5. Quick Note -->
                <div class="help-section" data-title="quick note notes" data-keywords="auto-save, save, counters, clear, status">
                    <button class="help-toggle" data-target="help-quicknote">
                        <span><i class="fas fa-sticky-note"></i> Quick Note</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-quicknote">
                        <p><strong>Overview:</strong> Jot down quick thoughts, ideas, or reminders instantly.</p>
                        <ul>
                            <li><strong>Write Freely</strong> — Simple text area for your notes</li>
                            <li><strong>Auto-Save</strong> — Your note automatically saves as you type (with debounce)</li>
                            <li><strong>Manual Save</strong> — Save instantly using the Save button</li>
                            <li><strong>Character & Word Counter</strong> — See your note length in real-time</li>
                            <li><strong>Clear Note</strong> — Clear the note with confirmation (saves as empty)</li>
                            <li><strong>Status Indicator</strong> — See when your note is saving, saved, or ready</li>
                            <li><strong>One Note Per User</strong> — Your note is personal and persistent across sessions</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+S / Cmd+S</span> Save note
                        </div>
                    </div>
                </div>

                <!-- 6. Activity History -->
                <div class="help-section" data-title="activity history activities" data-keywords="log, grouped by date, search, recent">
                    <button class="help-toggle" data-target="help-activity">
                        <span><i class="fas fa-history"></i> Activity History</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-activity">
                        <p><strong>Overview:</strong> View a complete log of your StudyDesk activity from the last 90 days.</p>
                        <ul>
                            <li><strong>Purpose</strong> — Tracks only meaningful, completion-based actions: tasks completed, exams created, topics completed, exams completed, and Pomodoro sessions. This keeps your activity log focused on real progress, rather than cluttering it with routine actions like switching tabs, editing tasks, or pausing timers.</li>
                            <li><strong>Activity Types</strong> — See tasks completed, exams created, topics completed, exams completed, and Pomodoro sessions</li>
                            <li><strong>Grouped by Date</strong> — Activities are organized by day for easy browsing</li>
                            <li><strong>Search</strong> — Filter activities by name or type</li>
                            <li><strong>Last 90 Days</strong> — Displays all activities from the last 90 days</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Clear search
                        </div>
                    </div>
                </div>

                <!-- 7. Profile -->
                <div class="help-section" data-title="profile account settings" data-keywords="update, change password, joined, username, email">
                    <button class="help-toggle" data-target="help-profile">
                        <span><i class="fas fa-user"></i> Profile</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-profile">
                        <p><strong>Overview:</strong> Manage your personal information and account settings.</p>
                        <ul>
                            <li><strong>View Account Info</strong> — See the details you provided when you created your account</li>
                            <li><strong>Update Profile</strong> — Change your username, email, phone, address, academic level, gender, and date of birth</li>
                            <li><strong>Change Password</strong> — Update your password securely (requires current password)</li>
                            <li><strong>Read-Only Fields</strong> — "Joined" date is displayed for reference (cannot be edited)</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+S / Cmd+S</span> Save profile changes
                            <span class="shortcut-key">Enter</span> Submit form (when in any input field)
                        </div>
                    </div>
                </div>

                <!-- 8. Feedback -->
                <div class="help-section" data-title="feedback bug report feature request support" data-keywords="submit, types, message, character limit">
                    <button class="help-toggle" data-target="help-feedback">
                        <span><i class="fas fa-comment"></i> Feedback</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-feedback">
                        <p><strong>Overview:</strong> Send feedback to help us improve StudyDesk.</p>
                        <ul>
                            <li><strong>Feedback Types</strong> — Choose from Bug Report, Feature Request, General Feedback, or Support</li>
                            <li><strong>Subject & Message</strong> — Provide a clear summary and detailed description</li>
                            <li><strong>Character Limit</strong> — Messages can be up to 1000 characters</li>
                            <li><strong>Success Confirmation</strong> — See a thank you message after submission</li>
                            <li><strong>Spam Prevention</strong> — You cannot send the exact same feedback again within 7 days to avoid duplicates</li>
                            <li><strong>Rate Limit</strong> — Only 3 feedback submissions are allowed per hour</li>
                            <li><strong>Cooldown Period</strong> — You must wait at least 5 minutes between each feedback submission</li>
                            <li><strong>One-Way Communication</strong> — Your feedback goes directly to the admin (no reply expected)</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+S / Cmd+S</span> Submit feedback
                        </div>
                    </div>
                </div>

                <!-- 9. Help & Support (This Page) -->
                <div class="help-section" data-title="help support" data-keywords="guide, documentation, search, accordion">
                    <button class="help-toggle" data-target="help-help">
                        <span><i class="fas fa-life-ring"></i> Help & Support</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-help">
                        <p><strong>Overview:</strong> A comprehensive guide to all StudyDesk features, including keyboard shortcuts.</p>
                        <ul>
                            <li><strong>Expandable Sections</strong> — Click any module to view its features and shortcuts</li>
                            <li><strong>Search</strong> — Filter help topics by name or content</li>
                            <li><strong>Keyboard Shortcuts</strong> — Quick reference for all modules</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Clear search
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="../js/notification.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/help.js"></script>
    <script src="../js/theme.js"></script>

</body>
</html>