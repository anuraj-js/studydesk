<?php
// pages/admin/admin_users.php

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
    <title>User Management - StudyDesk Admin</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_users.css">
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
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1>User Management</h1>
                    <p class="page-subtitle">View, search, and manage all registered users</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="admin-search">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search users by username or email...">
                    <button id="clearSearchBtn" class="clear-search" style="display:none;" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="admin-filters">
                <div class="filter-group">
                    <label for="roleFilter">
                        <i class="fas fa-user-tag"></i> Role
                    </label>
                    <select id="roleFilter">
                        <option value="all">All Roles</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="levelFilter">
                        <i class="fas fa-graduation-cap"></i> Academic Level
                    </label>
                    <select id="levelFilter">
                        <option value="all">All Levels</option>
                        <option value="school">School Level</option>
                        <option value="plus_two">+2 Level</option>
                        <option value="bachelor">Bachelor Level</option>
                        <option value="master">Master Level</option>
                        <option value="others">Others</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th style="width: 100px;">Role</th>
                            <th style="width: 140px;">Joined</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="6">
                                <div class="loading-state">
                                    <i class="fas fa-spinner fa-spin"></i> Loading users...
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

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay-custom" id="deleteModal">
        <div class="modal-box">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle" style="color: var(--color-error);"></i>
            </div>
            <div class="modal-title">Delete User</div>
            <div class="modal-message">
                This will permanently delete <strong id="deleteTargetName">user</strong> and all their data:
                <br>
                <small>Tasks, Exams, Topics, Quick Notes, Activity History, Feedback</small>
            </div>
            <div class="modal-input-group">
                <label for="deleteConfirmInput">Type the username to confirm:</label>
                <input type="text" id="deleteConfirmInput" placeholder="Type username here..." autocomplete="off">
            </div>
            <div class="modal-actions">
                <button class="btn btn-cancel" id="deleteCancelBtn">Cancel</button>
                <button class="btn btn-danger" id="deleteConfirmBtn" disabled>Delete User</button>
            </div>
        </div>
    </div>

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/admin/admin_users.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>