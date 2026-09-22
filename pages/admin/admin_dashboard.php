<?php
require_once(__DIR__ . "/../../config/gatekeeper_admin.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudyDesk</title>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1>Admin Dashboard</h1>
            <div class="header-actions">
                <span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../../api/auth/logout.php" class="btn-danger">Logout</a>
            </div>
        </header>
        <main>
            <h2>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <p>Welcome to your Admin Dashboard.</p>
        </main>
    </div>
</body>
</html>