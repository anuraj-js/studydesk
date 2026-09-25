<?php
// api/exams/topics_complete.php

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
$topicId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($topicId <= 0) {
    echo json_encode(["success" => false, "message" => "Topic ID required", "data" => null]);
    exit();
}

try {
    $sql = "SELECT topics.id, topics.exam_id, topics.title, exams.user_id, exams.exam_name FROM topics JOIN exams ON topics.exam_id = exams.id WHERE topics.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $topicId]);
    $topic = $stmt->fetch();

    if (!$topic) {
        echo json_encode(["success" => false, "message" => "Topic not found", "data" => null]);
        exit();
    }

    if ($topic["user_id"] != $userId) {
        echo json_encode(["success" => false, "message" => "You do not have permission to complete this topic", "data" => null]);
        exit();
    }

    $examId = $topic["exam_id"];
    $title = $topic["title"];
    $examName = $topic["exam_name"];

    $pdo->beginTransaction();

    $sql = "UPDATE exams SET completed_topics = completed_topics + 1 WHERE id = :exam_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["exam_id" => $examId]);

    $sql = "INSERT INTO activity_history (user_id, activity_type, related_item_name, additional_details) VALUES (:user_id, 'topic_completed', :item_name, :details)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "user_id" => $userId,
        "item_name" => $title,
        "details" => json_encode([
            "exam_id" => $examId,
            "exam_name" => $examName
        ])
    ]);

    $sql = "DELETE FROM topics WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $topicId]);

    $pdo->commit();

    $sql = "SELECT total_topics, completed_topics FROM exams WHERE id = :exam_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["exam_id" => $examId]);
    $counts = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Topic completed successfully",
        "data" => [
            "exam_id" => $examId,
            "total_topics" => $counts["total_topics"],
            "completed_topics" => $counts["completed_topics"]
        ]
    ]);
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    exit();
}
?>