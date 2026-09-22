<?php
// index.php

session_start();

if (empty($_SESSION["user_id"])) {
    header("Location: forms/login.html");
    exit();
}

// Role must exist and be valid
if (empty($_SESSION["role"])) {
    // Log this unexpected state or destroy session
    session_destroy();
    header("Location: forms/login.html");
    exit();
}

$role = $_SESSION["role"];

if ($role === "admin") {
    header("Location: pages/admin/admin_dashboard.php");
    exit();
}

if ($role === "user") {
    header("Location: pages/dashboard.php");
    exit();
}

// Unknown role - destroy session and redirect to login
session_destroy();
header("Location: forms/login.html");
exit();
?>