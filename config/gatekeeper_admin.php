<?php
// config/gatekeeper_admin.php - Restricts access to admin users only

session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /studydesk/forms/login.html");
    exit();
}

// Role must exist and be exactly 'admin'
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    session_destroy();
    header("Location: /studydesk/forms/login.html");
    exit();
}
?>