<?php
// pages/admin/admin_profile.php

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
    <title>Profile - StudyDesk Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_profile.css">
    <link rel="stylesheet" href="../../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/admin_header.php"); ?>
        <?php include(__DIR__ . "/includes/admin_sidebar.php"); ?>

        <main class="admin-page">
            <div class="page-header">
                <div class="page-header-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h1>My Profile</h1>
                    <p class="page-subtitle">Manage your account information</p>
                </div>
            </div>

            <div class="profile-container">
                <form id="profileForm" novalidate>
                    <!-- Joined (read-only) -->
                    <div class="profile-field">
                        <label>Joined</label>
                        <span id="profileJoined" class="profile-value" data-readonly="true">Loading...</span>
                    </div>

                    <!-- Username -->
                    <div class="profile-field">
                        <label for="profileUsername">Username</label>
                        <input type="text" id="profileUsername" placeholder="Username">
                    </div>

                    <!-- Email -->
                    <div class="profile-field">
                        <label for="profileEmail">Email</label>
                        <input type="email" id="profileEmail" placeholder="Email">
                    </div>

                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                        <span id="saveProfileText"><i class="fas fa-save"></i> Save Changes</span>
                        <span id="saveProfileSpinner" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    </button>
                </form>

                <!-- Change Password -->
                <div class="password-section">
                    <button type="button" class="password-toggle-btn" id="togglePasswordForm">
                        <i class="fas fa-key"></i> Change Password
                    </button>

                    <div class="password-form" id="passwordFormWrapper" style="display: none;">
                        <form id="passwordForm">
                            <div class="profile-field">
                                <label for="currentPassword">Current Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="currentPassword" placeholder="Enter current password">
                                    <button type="button" class="toggle-password" data-target="currentPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="profile-field">
                                <label for="newPassword">New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="newPassword" placeholder="Enter new password">
                                    <button type="button" class="toggle-password" data-target="newPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="profile-field">
                                <label for="confirmPassword">Confirm New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirmPassword" placeholder="Confirm new password">
                                    <button type="button" class="toggle-password" data-target="confirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" id="updatePasswordBtn">
                                <span id="updatePasswordText"><i class="fas fa-key"></i> Update Password</span>
                                <span id="updatePasswordSpinner" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Updating...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/validator.js"></script>
    <script src="../../js/admin/admin_profile.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>