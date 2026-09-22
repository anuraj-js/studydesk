<?php
// api/tasks/tasks.php

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
        // Get sort parameter from URL (default: due_date_asc)
        $sort = isset($_GET["sort"]) ? $_GET["sort"] : "due_date_asc";

        // Map sort options to SQL ORDER BY clauses
        $orderByMap = [
            "priority" => "FIELD(priority, 'high', 'medium', 'low'), due_date ASC",
            "due_date_asc" => "due_date ASC",
            "due_date_desc" => "due_date DESC",
            "type" => "type ASC, due_date ASC"
        ];

        // Fallback to default if invalid sort value
        $orderBy = isset($orderByMap[$sort]) ? $orderByMap[$sort] : $orderByMap["due_date_asc"];

        $sql = "SELECT id, title, description, type, priority, due_date, created_at 
                FROM tasks 
                WHERE user_id = :user_id 
                ORDER BY $orderBy";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(["user_id" => $userId]);
        $rows = $stmt->fetchAll();

        echo json_encode([
            "success" => true,
            "message" => "Tasks fetched successfully",
            "data" => ["tasks" => $rows]
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
        $title = isset($data["title"]) ? trim($data["title"]) : "";
        $type = isset($data["type"]) ? $data["type"] : "";
        $priority = isset($data["priority"]) ? $data["priority"] : "medium";
        $dueDate = isset($data["due_date"]) ? $data["due_date"] : "";
        $description = isset($data["description"]) ? (trim($data["description"]) ?: null) : null;

        if (empty($title)) {
            echo json_encode(["success" => false, "message" => "Title is required", "data" => null]);
            exit();
        }

        $validTypes = ["assignment", "study", "coding", "others"];
        if (!in_array($type, $validTypes)) {
            echo json_encode(["success" => false, "message" => "Invalid task type", "data" => null]);
            exit();
        }

        $validPriorities = ["low", "medium", "high"];
        if (!in_array($priority, $validPriorities)) {
            $priority = "medium";
        }

        $date = DateTime::createFromFormat("Y-m-d", $dueDate);
        if (!$date || $date->format("Y-m-d") !== $dueDate) {
            echo json_encode(["success" => false, "message" => "Invalid due date format", "data" => null]);
            exit();
        }

        $today = date('Y-m-d');
        if ($dueDate < $today) {
            echo json_encode(["success" => false, "message" => "Due date cannot be in the past", "data" => null]);
            exit();
        }

        $sql = "INSERT INTO tasks (user_id, title, description, type, priority, due_date) VALUES (:user_id, :title, :description, :type, :priority, :due_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "user_id"     => $userId,
            "title"       => $title,
            "description" => $description,
            "type"        => $type,
            "priority"    => $priority,
            "due_date"    => $dueDate
        ]);
        $taskId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT id, title, description, type, priority, due_date, created_at FROM tasks WHERE id = :id");
        $stmt->execute(["id" => $taskId]);
        $task = $stmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Task created successfully",
            "data"    => ["task" => $task]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "PUT") {
    try {
        $taskId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
        if ($taskId <= 0) {
            echo json_encode(["success" => false, "message" => "Task ID required", "data" => null]);
            exit();
        }

        $stmt = $pdo->prepare("SELECT user_id FROM tasks WHERE id = :id");
        $stmt->execute(["id" => $taskId]);
        $task = $stmt->fetch();

        if (!$task) {
            echo json_encode(["success" => false, "message" => "Task not found", "data" => null]);
            exit();
        }

        if ($task["user_id"] != $userId) {
            echo json_encode(["success" => false, "message" => "You do not have permission to edit this task", "data" => null]);
            exit();
        }

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $fields = [];
        $params = [];

        if (isset($data["title"])) {
            $title = trim($data["title"]);
            if (empty($title)) {
                echo json_encode(["success" => false, "message" => "Title cannot be empty", "data" => null]);
                exit();
            }
            $fields[] = "title = :title";
            $params[":title"] = $title;
        }

        if (isset($data["type"])) {
            $validTypes = ["assignment", "study", "coding", "others"];
            if (!in_array($data["type"], $validTypes)) {
                echo json_encode(["success" => false, "message" => "Invalid task type", "data" => null]);
                exit();
            }
            $fields[] = "type = :type";
            $params[":type"] = $data["type"];
        }

        if (isset($data["priority"])) {
            $validPriorities = ["low", "medium", "high"];
            if (!in_array($data["priority"], $validPriorities)) {
                echo json_encode(["success" => false, "message" => "Invalid priority", "data" => null]);
                exit();
            }
            $fields[] = "priority = :priority";
            $params[":priority"] = $data["priority"];
        }

        if (isset($data["due_date"])) {
            $date = DateTime::createFromFormat("Y-m-d", $data["due_date"]);
            if (!$date || $date->format("Y-m-d") !== $data["due_date"]) {
                echo json_encode(["success" => false, "message" => "Invalid due date format", "data" => null]);
                exit();
            }
            $today = date('Y-m-d');
            if ($data["due_date"] < $today) {
                echo json_encode(["success" => false, "message" => "Due date cannot be in the past", "data" => null]);
                exit();
            }
            $fields[] = "due_date = :due_date";
            $params[":due_date"] = $data["due_date"];
        }

        if (array_key_exists("description", $data)) {
            $fields[] = "description = :description";
            $params[":description"] = isset($data["description"]) ? (trim($data["description"]) ?: null) : null;
        }

        if (empty($fields)) {
            echo json_encode(["success" => false, "message" => "No fields to update", "data" => null]);
            exit();
        }

        $params[":id"] = $taskId;
        $sql = "UPDATE tasks SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare("SELECT id, title, description, type, priority, due_date, created_at FROM tasks WHERE id = :id");
        $stmt->execute(["id" => $taskId]);
        $updated = $stmt->fetch();

        echo json_encode([
            "success" => true,
            "message" => "Task updated successfully",
            "data"    => ["task" => $updated]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

if ($method === "DELETE") {
    try {
        $taskId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
        if ($taskId <= 0) {
            echo json_encode(["success" => false, "message" => "Task ID required", "data" => null]);
            exit();
        }

        $stmt = $pdo->prepare("SELECT user_id FROM tasks WHERE id = :id");
        $stmt->execute(["id" => $taskId]);
        $task = $stmt->fetch();

        if (!$task) {
            echo json_encode(["success" => false, "message" => "Task not found", "data" => null]);
            exit();
        }

        if ($task["user_id"] != $userId) {
            echo json_encode(["success" => false, "message" => "You do not have permission to delete this task", "data" => null]);
            exit();
        }

        $pdo->prepare("DELETE FROM tasks WHERE id = :id")->execute(["id" => $taskId]);

        echo json_encode([
            "success" => true,
            "message" => "Task deleted successfully",
            "data"    => null
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again.", "data" => null]);
    }
    exit();
}

echo json_encode(["success" => false, "message" => "Method not allowed", "data" => null]);
exit();
?>