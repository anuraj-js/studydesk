<?php
// Enforce error handling rules
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Enforce UTC timezone for PHP
date_default_timezone_set('UTC');

// Define the base URL for the application
define('BASE_URL', '/studydesk');

// Load environment variables
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    error_log('Environment file missing: ' . $envPath);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.', 'data' => null]);
    exit;
}

$env = parse_ini_file($envPath);
if ($env === false) {
    error_log('Failed to parse environment file.');
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.', 'data' => null]);
    exit;
}

/**
 * Creates a single PDO connection to the database.
 *
 * @param string $host
 * @param string $dbname
 * @param string $user
 * @param string $pass
 * @return PDO
 */
function get_db_connection(string $host, string $dbname, string $user, string $pass): PDO {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Force MySQL to UTC to match PHP
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"
    ];
    
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new RuntimeException('Database connection failed.');
    }
}

// Initialize the single database connection
$pdo = get_db_connection(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_NAME'] ?? 'studydesk',
    $env['DB_USER'] ?? 'root',
    $env['DB_PASS'] ?? ''
);