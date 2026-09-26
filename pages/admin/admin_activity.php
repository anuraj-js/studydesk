<?php
// pages/admin/admin_activity.php

require_once(__DIR__ . "/../../config/gatekeeper_admin.php");
require_once(__DIR__ . "/../../config/theme.php");

$theme = $_SESSION['theme'] ?? 'blue';
$darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1;

// Fetch users for filter dropdown
$usersStmt = $pdo->prepare("SELECT id, username FROM users ORDER BY username ASC");
$usersStmt->execute();
$usersList = $usersStmt->fetchAll();

// Get selected user from URL (if any)
$selectedUserId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>" data-dark="<?php echo $darkMode ? 'true' : 'false'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - StudyDesk Admin</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_activity.css">
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
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h1>Activity Log</h1>
                    <p class="page-subtitle">View all user activity across the platform</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="admin-search">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search activities by item name or type...">
                    <button id="clearSearchBtn" class="clear-search" style="display:none;" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="admin-filters">
                <div class="filter-group">
                    <label for="userFilter">
                        <i class="fas fa-user"></i> User
                    </label>
                    <select id="userFilter">
                        <option value="0">All Users</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?php echo (int)$u['id']; ?>" <?php echo $selectedUserId === (int)$u['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="typeFilter">
                        <i class="fas fa-filter"></i> Activity Type
                    </label>
                    <select id="typeFilter">
                        <option value="all">All Types</option>
                        <option value="task_completed">Task Completed</option>
                        <option value="exam_created">Exam Created</option>
                        <option value="topic_completed">Topic Completed</option>
                        <option value="exam_completed">Exam Completed</option>
                        <option value="pomodoro_completed">Pomodoro Session</option>
                    </select>
                </div>
            </div>

            <!-- Activity Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">User</th>
                            <th style="width: 180px;">Type</th>
                            <th>Item</th>
                            <th style="width: 180px;">Date</th>
                            <th style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activityTableBody">
                        <tr>
                            <td colspan="5">
                                <div class="loading-state">
                                    <i class="fas fa-spinner fa-spin"></i> Loading activity...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="pagination-container"></div>
        </main>
    </div>

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/admin/admin_activity.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>