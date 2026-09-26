<?php
// pages/admin/admin_user_profile.php

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
    <title>User Profile - StudyDesk Admin</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link rel="stylesheet" href="../../css/admin/admin_user_profile.css">
    <link rel="stylesheet" href="../../css/notification.css">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="app-container">
        <?php include(__DIR__ . "/includes/admin_header.php"); ?>
        <?php include(__DIR__ . "/includes/admin_sidebar.php"); ?>

        <main class="admin-page">
            <div id="userProfileContainer">
                <!-- Profile content rendered by JS -->
            </div>
        </main>
    </div>

    <script src="../../js/notification.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="../../js/admin/admin_user_profile.js"></script>
    <script src="../../js/theme.js"></script>
</body>
</html>