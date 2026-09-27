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

    // Verify exam has at least 1 topic total
    if ($exam["total_topics"] == 0) {
        echo json_encode(["success" => false, "message" => "Exam has no topics to complete", "data" => null]);
        exit();
    }

    // Verify all topics are completed
    if ($exam["completed_topics"] < $exam["total_topics"]) {
        echo json_encode(["success" => false, "message" => "All topics must be completed first", "data" => null]);
        exit();
    }

    // Fetch topic titles for activity history (may be empty if all topics completed)
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

    // Delete any remaining topics (should be none, but safe to clean up)
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
            "total_topics" => (int)$exam["total_topics"],
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
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>