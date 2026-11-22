<?php
require_once __DIR__ . '/includes/csrf.php';

if (empty($_SESSION['user_id'])) {
    header('Location: pages/login.php');
    exit();
}

if (empty($_SESSION['role'])) {
    session_destroy();
    header('Location: pages/login.php');
    exit();
}

$role = $_SESSION['role'];

if ($role === 'admin') {
    header('Location: pages/admin/home.php');
    exit();
}

if ($role === 'user') {
    header('Location: pages/home.php');
    exit();
}

// Unknown role — destroy session and redirect
session_destroy();
header('Location: pages/login.php');
exit();