<?php
// config/gatekeeper_user.php - Restricts access to regular users only

session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /studydesk/forms/login.html");
    exit();
}

// Role must exist and be exactly 'user'
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    session_destroy();
    header("Location: /studydesk/forms/login.html");
    exit();
}
?>