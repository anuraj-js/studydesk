<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');

/**
 * Returns a JSON response and exits.
 *
 * @param array|object|null $data
 * @param string $message
 * @param bool $success
 * @param int $status
 * @return void
 */
function json_response(mixed $data, string $message, bool $success, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $success ? $data : null
    ]);
    exit;
}

/**
 * Sends a standard 500 error response and logs the exception.
 *
 * @param Throwable $e
 * @return void
 */
function json_error(Throwable $e): void {
    error_log('API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    json_response(null, 'Something went wrong. Please try again.', false, 500);
}