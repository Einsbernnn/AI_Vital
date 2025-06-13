<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Read the BP values from the JSON file
$bpFile = 'bp_values.json';

// Debug information
$debug = [
    'file_exists' => file_exists($bpFile),
    'file_path' => realpath($bpFile),
    'file_permissions' => file_exists($bpFile) ? substr(sprintf('%o', fileperms($bpFile)), -4) : 'N/A'
];

if (file_exists($bpFile)) {
    $content = file_get_contents($bpFile);
    $data = json_decode($content, true);
    
    // Ensure error_message is included in the response
    if (!isset($data['error_message'])) {
        $data['error_message'] = '';
    }
    
    // Add debug info to response
    $data['debug'] = $debug;
    
    echo json_encode($data);
} else {
    echo json_encode([
        'systolic' => '',
        'diastolic' => '',
        'error_message' => '',
        'debug' => $debug
    ]);
}
?> 