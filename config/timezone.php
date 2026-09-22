<?php
// config/timezone.php

function applyUserTimezone(PDO $pdo, int $userId): string {
    $stmt = $pdo->prepare("SELECT timezone FROM users WHERE id = :user_id");
    $stmt->execute(["user_id" => $userId]);
    $user = $stmt->fetch();

    $timezone = $user["timezone"] ?? "UTC";

    // Validate — fallback to UTC if stored value is somehow invalid
    if (!in_array($timezone, timezone_identifiers_list())) {
        $timezone = "UTC";
    }

    // Set PHP timezone
    date_default_timezone_set($timezone);

    // Convert to MySQL offset format e.g. "+05:45"
    $dt     = new DateTime("now", new DateTimeZone($timezone));
    $offset = $dt->format("P");
    $pdo->exec("SET time_zone = '$offset'");

    return $timezone;
}
?>