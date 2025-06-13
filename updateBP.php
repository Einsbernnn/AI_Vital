<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the raw POST data
$raw_post = file_get_contents('php://input');
error_log("Raw POST data: " . $raw_post);
error_log("POST array: " . print_r($_POST, true));

// Get BP values from POST request
$systolic = isset($_POST['systolic']) ? $_POST['systolic'] : '';
$diastolic = isset($_POST['diastolic']) ? $_POST['diastolic'] : '';
$error_message = isset($_POST['error_message']) ? $_POST['error_message'] : '';

// Debug information
$debug = [
    'received_systolic' => $systolic,
    'received_diastolic' => $diastolic,
    'received_error' => $error_message,
    'server_software' => $_SERVER['SERVER_SOFTWARE'],
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME'],
    'current_dir' => getcwd()
];

// Store the values in a text file
$data = [
    'systolic' => $systolic,
    'diastolic' => $diastolic,
    'timestamp' => date('Y-m-d H:i:s'),
    'error_message' => $error_message
];

// Try multiple possible paths
$possible_paths = [
    __DIR__ . '/bp_values.json',
    dirname(__DIR__) . '/bp_values.json',
    $_SERVER['DOCUMENT_ROOT'] . '/StazSys/bp_values.json',
    'bp_values.json'
];

$success = false;
$last_error = null;

foreach ($possible_paths as $path) {
    $debug['trying_path'] = $path;
    $result = file_put_contents($path, json_encode($data));
    
    if ($result !== false) {
        $success = true;
        $debug['success_path'] = $path;
        break;
    } else {
        $last_error = error_get_last();
        $debug['failed_paths'][$path] = $last_error;
    }
}

if ($success) {
    echo json_encode([
        'status' => 'success',
        'message' => 'BP values updated successfully',
        'debug' => $debug
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to write BP values',
        'debug' => $debug,
        'last_error' => $last_error
    ]);
}
?> 