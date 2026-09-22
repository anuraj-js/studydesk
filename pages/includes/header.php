<!-- pages/includes/header.php -->

<?php
$theme = $_SESSION['theme'] ?? 'blue';
$logoFile = 'studydesk_logo_' . $theme . '.svg';
?>
<header class="app-header">
    <a href="dashboard.php" class="logo">
        <img src="../images/<?php echo $logoFile; ?>" alt="StudyDesk" class="logo-img">
    </a>
    <button class="menu-btn" id="menuBtn">
        <i class="fas fa-bars"></i>
    </button>
</header>