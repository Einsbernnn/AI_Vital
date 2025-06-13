<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get BP values from POST request
$systolic = isset($_POST['systolic']) ? intval($_POST['systolic']) : 0;
$diastolic = isset($_POST['diastolic']) ? intval($_POST['diastolic']) : 0;

if ($systolic <= 0 || $diastolic <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid BP values']);
    exit;
}

// Format BP value as "systolic/diastolic"
$bp_value = $systolic . '/' . $diastolic;

// Store the values in a text file
$data = [
    'bp' => $bp_value,
    'timestamp' => date('Y-m-d H:i:s')
];

file_put_contents('bp_values.json', json_encode($data));

echo json_encode(['status' => 'success', 'message' => 'BP values updated successfully', 'bp' => $bp_value]);
?> 