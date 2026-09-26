<?php
// pages/admin/includes/admin_header.php

require_once(__DIR__ . "/../../../config/gatekeeper_admin.php");

$theme = $_SESSION['theme'] ?? 'blue';
$logoFile = 'studydesk_logo_' . $theme . '.svg';
?>
<header class="app-header">
    <a href="admin_dashboard.php" class="logo">
        <img src="../../images/<?php echo $logoFile; ?>" alt="StudyDesk Admin" class="logo-img">
    </a>
    <button class="menu-btn" id="menuBtn" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
</header>