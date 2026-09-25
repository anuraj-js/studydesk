<?php
// api/exams/complete.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];
$examId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($examId <= 0) {
    echo json_encode(["success" => false, "message" => "Exam ID required", "data" => null]);
    exit();
}

try {
    // Fetch exam details
    $sql = "SELECT id, exam_name, subject, exam_date, total_topics, completed_topics, created_at FROM exams WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $examId, "user_id" => $userId]);
    $exam = $stmt->fetch();

    if (!$exam) {
        echo json_encode(["success" => false, "message" => "Exam not found", "data" => null]);
        exit();
    }

    // Count remaining topics from topics table (single query)
    $sql = "SELECT COUNT(*) as remaining FROM topics WHERE exam_id = :exam_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["exam_id" => $examId]);
    $result = $stmt->fetch();
    $remainingTopics = (int)$result["remaining"];

    // If no topics exist, exam has no topics to complete
    if ($remainingTopics == 0) {
        echo json_encode(["success" => false, "message" => "Exam has no topics to complete", "data" => null]);
        exit();
    }

    // If topics exist, they are all incomplete (topics are deleted when completed)
    // So any remaining topics means the exam is not fully completed
    // No need for a second COUNT query

    // Get topic titles for activity history
    $sql = "SELECT title FROM topics WHERE exam_id = :exam_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["exam_id" => $examId]);
    $topics = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $createdAt = new DateTime($exam["created_at"]);
    $examDate = new DateTime($exam["exam_date"]);
    $prepDuration = $createdAt->diff($examDate)->days;

    $today = new DateTime();
    $daysRemaining = $today->diff($examDate)->days;
    if ($examDate < $today) {
        $daysRemaining = -$daysRemaining;
    }

    $pdo->beginTransaction();

    // Delete all topics
    $sql = "DELETE FROM topics WHERE exam_id = :exam_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["exam_id" => $examId]);

    // Insert activity history
    $sql = "INSERT INTO activity_history (user_id, activity_type, related_item_name, additional_details) VALUES (:user_id, 'exam_completed', :item_name, :details)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "user_id" => $userId,
        "item_name" => $exam["exam_name"],
        "details" => json_encode([
            "exam_date" => $exam["exam_date"],
            "topics" => $topics,
            "prep_duration_days" => $prepDuration,
            "days_remaining" => $daysRemaining
        ])
    ]);

    // Delete the exam
    $sql = "DELETE FROM exams WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $examId]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Exam completed successfully",
        "data" => null
    ]);
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>