<?php
// api/exams/exams.php

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
        // Get sort parameter from URL (default: exam_date_asc)
        $sort = isset($_GET["sort"]) ? $_GET["sort"] : "exam_date_asc";

        // Map sort options to SQL ORDER BY clauses
        $orderByMap = [
            "exam_date_asc" => "exam_date ASC",
            "exam_date_desc" => "exam_date DESC",
            "exam_name" => "exam_name ASC",
            "subject" => "subject ASC",
            "progress" => "CASE WHEN total_topics = 0 THEN 0 ELSE completed_topics * 100 / total_topics END DESC, exam_date ASC"
        ];

        // Fallback to default if invalid sort value
        $orderBy = isset($orderByMap[$sort]) ? $orderByMap[$sort] : $orderByMap["exam_date_asc"];

        $sql = "SELECT id, exam_name, subject, exam_date, total_topics, completed_topics, created_at 
                FROM exams 
                WHERE user_id = :user_id 
                ORDER BY $orderBy";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(["user_id" => $userId]);
        $rows = $stmt->fetchAll();

        echo json_encode([
            "success" => true,
            "message" => "Exams fetched successfully",
            "data" => ["exams" => $rows]
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

        // Null-safe input handling
        $examName = isset($data["exam_name"]) ? trim($data["exam_name"]) : "";
        $subject = isset($data["subject"]) ? trim($data["subject"]) : "";
        $examDate = isset($data["exam_date"]) ? $data["exam_date"] : "";

        if (empty($examName)) {
            echo json_encode(["success" => false, "message" => "Exam name is required", "data" => null]);
            exit();
        }

        if (empty($subject)) {
            echo json_encode(["success" => false, "message" => "Subject is required", "data" => null]);
            exit();
        }

        $date = DateTime::createFromFormat("Y-m-d", $examDate);
        if (!$date || $date->format("Y-m-d") !== $examDate) {
            echo json_encode(["success" => false, "message" => "Invalid exam date format", "data" => null]);
            exit();
        }

        $today = date('Y-m-d');
        if ($examDate < $today) {
            echo json_encode(["success" => false, "message" => "Exam date cannot be in the past", "data" => null]);
            exit();
        }

        $sql = "INSERT INTO exams (user_id, exam_name, subject, exam_date, total_topics, completed_topics) VALUES (:user_id, :exam_name, :subject, :exam_date, 0, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "user_id" => $userId,
            "exam_name" => $examName,
            "subject" => $subject,
            "exam_date" => $examDate
        ]);
        $examId = $pdo->lastInsertId();

        $sql = "INSERT INTO activity_history (user_id, activity_type, related_item_name, additional_details) VALUES (:user_id, 'exam_created', :item_name, :details)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "user_id" => $userId,
            "item_name" => $examName,
            "details" => json_encode(["exam_date" => $examDate])
        ]);

        $sql = "SELECT id, exam_name, subject, exam_date, total_topics, completed_topics, created_at FROM exams WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $examId]);
        $exam = $stmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Exam created successfully",
            "data" => ["exam" => $exam]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "PUT") {
    try {
        $examId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
        if ($examId <= 0) {
            echo json_encode(["success" => false, "message" => "Exam ID required", "data" => null]);
            exit();
        }

        $sql = "SELECT id, user_id FROM exams WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $examId]);
        $exam = $stmt->fetch();

        if (!$exam) {
            echo json_encode(["success" => false, "message" => "Exam not found", "data" => null]);
            exit();
        }

        if ($exam["user_id"] != $userId) {
            echo json_encode(["success" => false, "message" => "You do not have permission to edit this exam", "data" => null]);
            exit();
        }

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $fields = [];
        $params = [];

        if (isset($data["exam_name"])) {
            $examName = trim($data["exam_name"]);
            if (empty($examName)) {
                echo json_encode(["success" => false, "message" => "Exam name cannot be empty", "data" => null]);
                exit();
            }
            $fields[] = "exam_name = :exam_name";
            $params[":exam_name"] = $examName;
        }

        if (isset($data["subject"])) {
            $subject = trim($data["subject"]);
            if (empty($subject)) {
                echo json_encode(["success" => false, "message" => "Subject cannot be empty", "data" => null]);
                exit();
            }
            $fields[] = "subject = :subject";
            $params[":subject"] = $subject;
        }

        if (isset($data["exam_date"])) {
            $date = DateTime::createFromFormat("Y-m-d", $data["exam_date"]);
            if (!$date || $date->format("Y-m-d") !== $data["exam_date"]) {
                echo json_encode(["success" => false, "message" => "Invalid exam date format", "data" => null]);
                exit();
            }
            $today = date('Y-m-d');
            if ($data["exam_date"] < $today) {
                echo json_encode(["success" => false, "message" => "Exam date cannot be in the past", "data" => null]);
                exit();
            }
            $fields[] = "exam_date = :exam_date";
            $params[":exam_date"] = $data["exam_date"];
        }

        if (empty($fields)) {
            echo json_encode(["success" => false, "message" => "No fields to update", "data" => null]);
            exit();
        }

        $params[":id"] = $examId;
        $sql = "UPDATE exams SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $sql = "SELECT id, exam_name, subject, exam_date, total_topics, completed_topics, created_at FROM exams WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $examId]);
        $updated = $stmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Exam updated successfully",
            "data" => ["exam" => $updated]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "DELETE") {
    try {
        $examId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
        if ($examId <= 0) {
            echo json_encode(["success" => false, "message" => "Exam ID required", "data" => null]);
            exit();
        }

        $sql = "SELECT id, user_id FROM exams WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $examId]);
        $exam = $stmt->fetch();

        if (!$exam) {
            echo json_encode(["success" => false, "message" => "Exam not found", "data" => null]);
            exit();
        }

        if ($exam["user_id"] != $userId) {
            echo json_encode(["success" => false, "message" => "You do not have permission to delete this exam", "data" => null]);
            exit();
        }

        $sql = "DELETE FROM exams WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $examId]);

        echo json_encode([
            "success" => true,
            "message" => "Exam deleted successfully",
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