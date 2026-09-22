<?php
// api/exams/topics.php

session_start();
header("Content-Type: application/json");
require_once(__DIR__ . "/../../config/db.php");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized", "data" => null]);
    exit();
}

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        $examId = isset($_GET["exam_id"]) ? (int)$_GET["exam_id"] : 0;
        if ($examId <= 0) {
            echo json_encode(["success" => false, "message" => "Exam ID is required", "data" => null]);
            exit();
        }

        $sql = "SELECT id FROM exams WHERE id = :exam_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["exam_id" => $examId, "user_id" => $userId]);
        $exam = $stmt->fetch();

        if (!$exam) {
            echo json_encode(["success" => false, "message" => "Exam not found", "data" => null]);
            exit();
        }

        $sql = "SELECT id, exam_id, title, created_at FROM topics WHERE exam_id = :exam_id ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["exam_id" => $examId]);
        $rows = $stmt->fetchAll();

        echo json_encode([
            "success" => true,
            "message" => "Topics fetched successfully",
            "data" => ["topics" => $rows]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "POST") {
    try {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $examId = isset($data["exam_id"]) ? (int)$data["exam_id"] : 0;
        $title = isset($data["title"]) ? trim($data["title"]) : "";

        if ($examId <= 0) {
            echo json_encode(["success" => false, "message" => "Exam ID is required", "data" => null]);
            exit();
        }

        if (empty($title)) {
            echo json_encode(["success" => false, "message" => "Topic name is required", "data" => null]);
            exit();
        }

        $sql = "SELECT id FROM exams WHERE id = :exam_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["exam_id" => $examId, "user_id" => $userId]);
        $exam = $stmt->fetch();

        if (!$exam) {
            echo json_encode(["success" => false, "message" => "Exam not found", "data" => null]);
            exit();
        }

        $sql = "INSERT INTO topics (exam_id, title) VALUES (:exam_id, :title)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["exam_id" => $examId, "title" => $title]);
        $topicId = $pdo->lastInsertId();

        $sql = "UPDATE exams SET total_topics = total_topics + 1 WHERE id = :exam_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["exam_id" => $examId]);

        $sql = "SELECT id, exam_id, title, created_at FROM topics WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $topicId]);
        $topic = $stmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Topic created successfully",
            "data" => ["topic" => $topic]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "PUT") {
    try {
        $topicId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
        if ($topicId <= 0) {
            echo json_encode(["success" => false, "message" => "Topic ID required", "data" => null]);
            exit();
        }

        $sql = "SELECT topics.id, topics.exam_id, exams.user_id FROM topics JOIN exams ON topics.exam_id = exams.id WHERE topics.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $topicId]);
        $topic = $stmt->fetch();

        if (!$topic) {
            echo json_encode(["success" => false, "message" => "Topic not found", "data" => null]);
            exit();
        }

        if ($topic["user_id"] != $userId) {
            echo json_encode(["success" => false, "message" => "You do not have permission to edit this topic", "data" => null]);
            exit();
        }

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (!isset($data["title"]) || empty(trim($data["title"]))) {
            echo json_encode(["success" => false, "message" => "No fields to update", "data" => null]);
            exit();
        }

        $title = trim($data["title"]);
        $sql = "UPDATE topics SET title = :title WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["title" => $title, "id" => $topicId]);

        $sql = "SELECT id, exam_id, title, created_at FROM topics WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $topicId]);
        $updated = $stmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Topic updated successfully",
            "data" => ["topic" => $updated]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "DELETE") {
    try {
        $topicId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
        if ($topicId <= 0) {
            echo json_encode(["success" => false, "message" => "Topic ID required", "data" => null]);
            exit();
        }

        $sql = "SELECT topics.id, topics.exam_id, exams.user_id FROM topics JOIN exams ON topics.exam_id = exams.id WHERE topics.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $topicId]);
        $topic = $stmt->fetch();

        if (!$topic) {
            echo json_encode(["success" => false, "message" => "Topic not found", "data" => null]);
            exit();
        }

        if ($topic["user_id"] != $userId) {
            echo json_encode(["success" => false, "message" => "You do not have permission to delete this topic", "data" => null]);
            exit();
        }

        $examId = $topic["exam_id"];

        $sql = "DELETE FROM topics WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $topicId]);

        // Update counters with GREATEST to prevent negative values
        $sql = "UPDATE exams SET 
            total_topics = GREATEST(total_topics - 1, 0),
            completed_topics = GREATEST(completed_topics - 1, 0)
        WHERE id = :exam_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["exam_id" => $examId]);

        echo json_encode([
            "success" => true,
            "message" => "Topic deleted successfully",
            "data" => null
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
exit();
?>