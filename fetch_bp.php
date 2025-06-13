<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Function to get the latest BP values
function getLatestBP() {
    if (file_exists('bp_values.json')) {
        $bp_data = json_decode(file_get_contents('bp_values.json'), true);
        error_log("BP Data from file: " . print_r($bp_data, true)); // Debug log
        if ($bp_data && isset($bp_data['bp'])) {
            return $bp_data['bp'];
        }
    }
    return null;
}

// Get BP value
$bp_value = getLatestBP();
error_log("BP Value being sent: " . $bp_value); // Debug log

// Send the response
echo json_encode(['bp' => $bp_value]);
?>