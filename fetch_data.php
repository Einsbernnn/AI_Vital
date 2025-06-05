<?php
header('Content-Type: application/json');

$esp32IP = "http://192.168.101.20"; // ESP32 IP Address
$data = null; // Default to null to detect if no data is received

// Fetch data from ESP32 using cURL
$ch = curl_init("$esp32IP/data");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Debugging: Log the raw response from ESP32
error_log("Raw ESP32 Response: " . $response);

if ($response) {
    $data = $response;
}

// Initialize default values
$body_temp = $ecg = $pulse_rate = $spo2 = $blood_pressure = null;

if ($data) {
    // Split the response into individual sensor values
    $values = explode(",", $data);

    if (count($values) >= 5) {
        list($body_temp, $ecg, $pulse_rate, $spo2, $blood_pressure) = $values;
    }

    // Debugging: Log the parsed values
    error_log("Parsed Values - Body Temp: $body_temp, ECG: $ecg, Pulse Rate: $pulse_rate, SpO2: $spo2, BP: $blood_pressure");

    // Ensure numeric values where needed
    $body_temp = is_numeric($body_temp) ? floatval($body_temp) : 0.00;
    $ecg = is_numeric($ecg) ? floatval($ecg) : 0.00;
    $pulse_rate = is_numeric($pulse_rate) ? floatval($pulse_rate) : 0.00;
    $spo2 = is_numeric($spo2) ? floatval($spo2) : 0.00;
    $blood_pressure = $blood_pressure ?: "N/A"; // If not available
}

// Format the current timestamp
$currentTime = date("Y-m-d H:i:s");

// Return the sensor data as JSON
echo json_encode([
    "body_temp" => $body_temp,
    "ecg" => $ecg,
    "pulse_rate" => $pulse_rate,
    "spo2" => $spo2,
    "bp" => $blood_pressure, // Use "bp" key for frontend compatibility
    "currentTime" => $currentTime
]);
?>