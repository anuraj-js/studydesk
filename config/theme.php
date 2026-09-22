<?php
// config/theme.php - Loads user theme preferences from database

require_once(__DIR__ . "/database_config.php");

// Auth guard - only proceed if user is logged in
if (empty($_SESSION['user_id'])) {
    return;
}

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare("SELECT theme, dark_mode FROM users WHERE id = :user_id");
    $stmt->execute(["user_id" => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['theme'] = $user['theme'] ?? 'blue';
        $_SESSION['dark_mode'] = $user['dark_mode'] ?? 0;  // 0 or 1 (integer)
    }

} catch (PDOException $e) {
    // Database error - use session defaults
    $_SESSION['theme'] = $_SESSION['theme'] ?? 'blue';
    $_SESSION['dark_mode'] = $_SESSION['dark_mode'] ?? 0;  // 0 (integer)
}
?>