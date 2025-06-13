<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get BP values from POST request
$systolic = isset($_POST['systolic']) ? $_POST['systolic'] : '';
$diastolic = isset($_POST['diastolic']) ? $_POST['diastolic'] : '';

if (empty($systolic) || empty($diastolic)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing BP values']);
    exit;
}

// Store the values in a text file
$data = [
    'systolic' => $systolic,
    'diastolic' => $diastolic,
    'timestamp' => date('Y-m-d H:i:s')
];

file_put_contents('bp_values.json', json_encode($data));

echo json_encode(['status' => 'success', 'message' => 'BP values updated successfully']);
?> 