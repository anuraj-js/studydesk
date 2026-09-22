<?php
// pages/task_manager.php

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
    <title>Task Manager - StudyDesk</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/tasks.css">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/header.php"); ?>
        <?php include(__DIR__ . "/includes/sidebar.php"); ?>

        <main class="task-manager">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-header-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <h1>Task Manager</h1>
                        <p class="page-subtitle">Organize and track your daily tasks</p>
                    </div>
                </div>
                <button id="openPanelBtn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Task
                </button>
            </div>

            <!-- Search Bar -->
            <div class="task-search">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="taskSearchInput" placeholder="Search tasks by name or description...">
                    <button id="clearSearchBtn" class="clear-search" style="display:none;" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <!-- Search Info -->
                <div id="searchInfo" class="search-info" style="display:none;"></div>
            </div>

            <!-- Task Controls: Sort + New Task -->
            <div class="task-manager-header">
                <h2>Your Tasks</h2>
                <div class="task-controls">
                    <div class="task-sort">
                        <label for="sortBy">
                            <i class="fas fa-sort"></i> Sort by:
                        </label>
                        <select id="sortBy">
                            <option value="priority">Priority (High → Low)</option>
                            <option value="due_date_asc" selected>Due Date (Earliest first)</option>
                            <option value="due_date_desc">Due Date (Latest first)</option>
                            <option value="type">Type (A → Z)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" data-tab="today" id="tab-today">
                    Today <span class="badge" id="badge-today">0</span>
                </button>
                <button class="tab-btn" data-tab="upcoming" id="tab-upcoming">
                    Upcoming <span class="badge" id="badge-upcoming">0</span>
                </button>
                <button class="tab-btn" data-tab="overdue" id="tab-overdue">
                    Overdue <span class="badge badge-danger" id="badge-overdue">0</span>
                </button>
            </div>

            <!-- Task Panes -->
            <div id="pane-today" class="task-pane active"></div>
            <div id="pane-upcoming" class="task-pane"></div>
            <div id="pane-overdue" class="task-pane"></div>
        </main>
    </div>

    <!-- Slide-in Panel -->
    <div id="taskPanel" class="slide-panel">
        <div class="panel-header">
            <h3 id="panelTitle">New Task</h3>
            <button id="closePanelBtn" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="taskForm" novalidate>
            <input type="hidden" id="taskId" value="">
            <div class="form-group">
                <label for="taskTitle">Task name</label>
                <input type="text" id="taskTitle" placeholder="e.g. Complete coding assignment" required>
            </div>
            <div class="form-group">
                <label for="taskType">Type</label>
                <select id="taskType" required>
                    <option value="assignment">Assignment</option>
                    <option value="study">Study</option>
                    <option value="coding">Coding</option>
                    <option value="others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="taskPriority">Priority</label>
                <select id="taskPriority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="form-group">
                <label for="taskDueDate">Due date</label>
                <input type="date" id="taskDueDate" required>
            </div>
            <div class="form-group">
                <label for="taskDescription">Description <span class="optional">(optional)</span></label>
                <textarea id="taskDescription" placeholder="Add details..." rows="4"></textarea>
            </div>
            <button type="submit" id="submitBtn" class="btn btn-primary">Add task</button>
        </form>
    </div>

    <div id="panelOverlay" class="panel-overlay"></div>

    <script src="../js/notification.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/tasks.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>