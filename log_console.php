<?php
// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get the message from POST data
$message = isset($_POST['message']) ? $_POST['message'] : '';

if (!empty($message)) {
    // Add timestamp to the message
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Append to log file
    file_put_contents('log_console.txt', $logMessage, FILE_APPEND);
    
    // Send success response
    echo json_encode(['status' => 'success']);
} else {
    // Send error response if no message
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No message provided']);
}
?> 