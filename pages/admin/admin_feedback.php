<?php
// pages/admin/admin_feedback.php

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
    <title>Feedback Management - StudyDesk Admin</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_feedback.css">
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
                    <i class="fas fa-comment"></i>
                </div>
                <div>
                    <h1>Feedback Management</h1>
                    <p class="page-subtitle">View and manage user feedback</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="admin-search">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search feedback by subject, message, or user...">
                    <button id="clearSearchBtn" class="clear-search" style="display:none;" aria-label="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Status Tabs (rendered by JS) -->
            <div class="status-tabs" id="statusTabs">
                <!-- Populated dynamically -->
            </div>

            <!-- Type Filter -->
            <div class="admin-filters">
                <div class="filter-group">
                    <label for="typeFilter">
                        <i class="fas fa-tag"></i> Type
                    </label>
                    <select id="typeFilter">
                        <option value="all">All Types</option>
                        <option value="bug_report">Bug Report</option>
                        <option value="feature_request">Feature Request</option>
                        <option value="general_feedback">General Feedback</option>
                        <option value="support">Support</option>
                    </select>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 130px;">User</th>
                            <th style="width: 150px;">Type</th>
                            <th>Subject</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 130px;">Date</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="feedbackTableBody">
                        <tr>
                            <td colspan="7">
                                <div class="loading-state">
                                    <i class="fas fa-spinner fa-spin"></i> Loading feedback...
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

    <!-- Feedback Detail Modal -->
    <div class="modal-overlay-custom" id="feedbackModal">
        <div class="modal-box modal-box-wide">
            <div class="modal-header">
                <h3 class="modal-header-title">
                    <i class="fas fa-comment"></i> Feedback Details
                </h3>
                <button class="modal-close-btn" id="modalCloseBtn" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-content">
                <div class="modal-meta-grid">
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">User</span>
                        <span class="modal-meta-value" id="modalUser">—</span>
                    </div>
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">Email</span>
                        <span class="modal-meta-value" id="modalEmail">—</span>
                    </div>
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">Type</span>
                        <span class="modal-meta-value" id="modalType">—</span>
                    </div>
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">Date</span>
                        <span class="modal-meta-value" id="modalDate">—</span>
                    </div>
                </div>

                <div class="modal-subject-section">
                    <span class="modal-meta-label">Subject</span>
                    <div class="modal-subject" id="modalSubject">—</div>
                </div>

                <div class="modal-message-section">
                    <span class="modal-meta-label">Message</span>
                    <div class="modal-message-body" id="modalBody">—</div>
                </div>

                <div class="modal-status-section">
                    <label for="modalStatusSelect" class="modal-meta-label">Status</label>
                    <select id="modalStatusSelect" class="modal-status-select">
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer-actions">
                <button class="btn btn-danger" id="modalDeleteBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button class="btn btn-primary" id="modalSaveBtn">
                    <i class="fas fa-save"></i> Save Status
                </button>
            </div>
        </div>
    </div>

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/admin/admin_feedback.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>