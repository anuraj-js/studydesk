<?php
// pages/admin/admin_help.php

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
    <title>Help & Support - StudyDesk Admin</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_help.css">
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
                    <i class="fas fa-life-ring"></i>
                </div>
                <div>
                    <h1>Help & Support</h1>
                    <p class="page-subtitle">Learn how to manage StudyDesk effectively</p>
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
            <div class="help-container">

                <!-- 1. Dashboard -->
                <div class="help-section" data-title="dashboard stats overview" data-keywords="stats, cards, quick links, users, tasks, exams, activity">
                    <button class="help-toggle" data-target="help-dashboard">
                        <span><i class="fas fa-chart-pie"></i> Dashboard</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-dashboard">
                        <p><strong>Overview:</strong> The Admin Dashboard gives you a quick system overview with key totals and quick access to management sections.</p>
                        <ul>
                            <li><strong>Stats Cards</strong> — View total counts for Users, Tasks, Exams, and Activity</li>
                            <li><strong>Quick Links</strong> — Fast access to Manage Users, View Feedback, and View Activity</li>
                            <li><strong>Welcome Greeting</strong> — Personalized welcome message with your admin username</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-none">None</span>
                        </div>
                    </div>
                </div>

                <!-- 2. User Management -->
                <div class="help-section" data-title="user management users list search delete" data-keywords="search, filter, role, level, pagination, view, delete">
                    <button class="help-toggle" data-target="help-users">
                        <span><i class="fas fa-users"></i> User Management</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-users">
                        <p><strong>Overview:</strong> View, search, filter, and manage all registered users on the platform.</p>
                        <ul>
                            <li><strong>View Users</strong> — See all users with ID, username, email, role, and joined date</li>
                            <li><strong>Search</strong> — Search users by username or email in real-time</li>
                            <li><strong>Filter by Role</strong> — Filter users by role (User / Admin)</li>
                            <li><strong>Filter by Academic Level</strong> — Filter users by their academic level</li>
                            <li><strong>Pagination</strong> — 20 users per page for easy browsing</li>
                            <li><strong>View Profile</strong> — Click the eye icon to see full user details</li>
                            <li><strong>Delete User</strong> — Remove a user (requires typing their username to confirm)</li>
                        </ul>
                        <div class="help-rules">
                            <p><strong><i class="fas fa-exclamation-triangle"></i> Safety Rules:</strong></p>
                            <ul>
                                <li>You cannot delete your own account</li>
                                <li>You cannot delete the last admin account</li>
                                <li>Deleting a user cascades to ALL their data (tasks, exams, notes, activity, feedback)</li>
                                <li>Deletion is permanent and cannot be undone</li>
                            </ul>
                        </div>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Clear search / Close modal
                        </div>
                    </div>
                </div>

                <!-- 3. User Profile View -->
                <div class="help-section" data-title="user profile view details account activity summary" data-keywords="profile, account, contact, activity, summary, quick note">
                    <button class="help-toggle" data-target="help-user-profile">
                        <span><i class="fas fa-user-circle"></i> User Profile View</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-user-profile">
                        <p><strong>Overview:</strong> View complete details of any user, including their activity summary.</p>
                        <ul>
                            <li><strong>Account Information</strong> — User ID, username, email, role, academic level, gender, DOB, joined date</li>
                            <li><strong>Contact Information</strong> — Phone and address (if provided)</li>
                            <li><strong>Activity Summary</strong> — Tasks completed, exams created, topics completed, exams completed, Pomodoro sessions</li>
                            <li><strong>Extra Stats</strong> — Total activities, active tasks, active exams, quick note presence, last active date</li>
                            <li><strong>View Full Activity</strong> — Button to see all activity of that specific user</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Escape</span> Close modal
                        </div>
                    </div>
                </div>

                <!-- 4. Activity Log -->
                <div class="help-section" data-title="activity log user activities tasks exams pomodoro" data-keywords="activity, search, filter, user, type, pagination">
                    <button class="help-toggle" data-target="help-activity">
                        <span><i class="fas fa-history"></i> Activity Log</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-activity">
                        <p><strong>Overview:</strong> View all user activities across the platform from the last 90 days.</p>
                        <ul>
                            <li><strong>View Activities</strong> — See all activity with user, type, item, and date</li>
                            <li><strong>Search</strong> — Search activities by item name or activity type</li>
                            <li><strong>Filter by User</strong> — Filter activities by a specific user</li>
                            <li><strong>Filter by Type</strong> — Filter by activity type (task completed, exam created, etc.)</li>
                            <li><strong>Pagination</strong> — 20 activities per page</li>
                            <li><strong>Activity Types</strong> — Task Completed, Exam Created, Topic Completed, Exam Completed, Pomodoro Session</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Clear search
                        </div>
                    </div>
                </div>

                <!-- 5. Feedback Management -->
                <div class="help-section" data-title="feedback management bug report feature request support" data-keywords="status, unread, read, resolved, filter, delete, view">
                    <button class="help-toggle" data-target="help-feedback">
                        <span><i class="fas fa-comment"></i> Feedback Management</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-feedback">
                        <p><strong>Overview:</strong> View and manage all user feedback submissions.</p>
                        <ul>
                            <li><strong>Status Tabs</strong> — Filter by status: All / Unread / Read / Resolved (with counts)</li>
                            <li><strong>Search</strong> — Search by subject, message, username, or email</li>
                            <li><strong>Filter by Type</strong> — Filter by feedback type (Bug Report, Feature Request, General Feedback, Support)</li>
                            <li><strong>View Details</strong> — Click the eye icon to see the full feedback in a modal</li>
                            <li><strong>Update Status</strong> — Change status from within the modal (Unread → Read → Resolved)</li>
                            <li><strong>Delete Feedback</strong> — Delete from the table or modal (with confirmation)</li>
                            <li><strong>Pagination</strong> — 20 feedback items per page</li>
                        </ul>
                        <div class="help-rules">
                            <p><strong><i class="fas fa-info-circle"></i> Status Workflow:</strong></p>
                            <ul>
                                <li><strong>Unread</strong> — New feedback (highlighted in list)</li>
                                <li><strong>Read</strong> — Acknowledged but not yet resolved</li>
                                <li><strong>Resolved</strong> — Issue addressed or feature considered</li>
                            </ul>
                        </div>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+F / Cmd+F</span> Focus search
                            <span class="shortcut-key">Escape</span> Close modal / Clear search
                        </div>
                    </div>
                </div>

                <!-- 6. Profile -->
                <div class="help-section" data-title="profile account settings update password" data-keywords="username, email, phone, address, academic level, gender, date of birth, change password">
                    <button class="help-toggle" data-target="help-profile">
                        <span><i class="fas fa-user"></i> My Profile</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-profile">
                        <p><strong>Overview:</strong> Manage your own admin account information.</p>
                        <ul>
                            <li><strong>View Account Info</strong> — See your username, email, role, and joined date</li>
                            <li><strong>Update Profile</strong> — Change your username, email, phone, address, academic level, gender, and date of birth</li>
                            <li><strong>Change Password</strong> — Update your password securely (requires current password)</li>
                            <li><strong>Read-Only Fields</strong> — "Joined" date cannot be edited</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Ctrl+S / Cmd+S</span> Save profile changes
                            <span class="shortcut-key">Escape</span> Close password form
                        </div>
                    </div>
                </div>

                <!-- 7. Themes & Appearance -->
                <div class="help-section" data-title="themes appearance dark mode colors" data-keywords="blue, purple, red, dark, light, sidebar">
                    <button class="help-toggle" data-target="help-themes">
                        <span><i class="fas fa-palette"></i> Themes & Appearance</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-themes">
                        <p><strong>Overview:</strong> Customize the look and feel of your admin panel.</p>
                        <ul>
                            <li><strong>Themes</strong> — Choose between Blue, Purple, or Red theme</li>
                            <li><strong>Dark Mode</strong> — Toggle dark mode for comfortable viewing at night</li>
                            <li><strong>Access</strong> — Located in the sidebar under "Appearance"</li>
                            <li><strong>Auto-Save</strong> — Theme preferences are saved to your account automatically</li>
                            <li><strong>Logo Updates</strong> — Admin logo changes with your theme</li>
                        </ul>
                        <div class="help-shortcuts">
                            <span class="shortcut-label">Keyboard Shortcuts:</span>
                            <span class="shortcut-key">Escape</span> Close theme options
                        </div>
                    </div>
                </div>

                <!-- 8. Help & Support (This Page) -->
                <div class="help-section" data-title="help support guide documentation" data-keywords="guide, documentation, search, accordion, shortcuts">
                    <button class="help-toggle" data-target="help-help">
                        <span><i class="fas fa-life-ring"></i> Help & Support</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="help-content" id="help-help">
                        <p><strong>Overview:</strong> A comprehensive guide to all admin features, including keyboard shortcuts.</p>
                        <ul>
                            <li><strong>Expandable Sections</strong> — Click any module to view its features and shortcuts</li>
                            <li><strong>Search</strong> — Filter help topics by name or content</li>
                            <li><strong>Keyboard Shortcuts</strong> — Quick reference for all admin modules</li>
                            <li><strong>Auto-Expand</strong> — When only one section matches your search, it opens automatically</li>
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

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/admin/admin_help.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>