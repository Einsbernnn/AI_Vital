<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database.php';

header('Content-Type: application/json'); // Ensure the response is JSON

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // Decode JSON input if Content-Type is application/json
    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'] ?? $_POST['id'] ?? null;
    $patient_name = $input['patient_name'] ?? $_POST['patient_name'] ?? null;
    $temperature = $input['body_temperature'] ?? $_POST['body_temperature'] ?? null;
    $ecg_rate = $input['ecg'] ?? $_POST['ecg'] ?? null;
    $pulse_rate = $input['pulse_rate'] ?? $_POST['pulse_rate'] ?? null;
    $spo2_level = $input['spo2'] ?? $_POST['spo2'] ?? null;
    $blood_pressure = $input['blood_pressure'] ?? $_POST['blood_pressure'] ?? null;
    $diagnosis = $input['diagnosis'] ?? $_POST['diagnosis'] ?? null;

    if (!$id || !$patient_name || !$temperature || !$ecg_rate || !$pulse_rate || !$spo2_level || !$blood_pressure || !$diagnosis) {
        throw new Exception("All fields are required.");
    }

    // Establish database connection
    $conn = Database::connect();

    // Insert the readings and diagnosis into the health_readings table
    $insertQuery = "INSERT INTO health_readings (id, patient_name, temperature, ecg_rate, pulse_rate, spo2_level, blood_pressure, diagnosis, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);

    if (!$stmt) {
        throw new Exception("Failed to prepare insert query: " . $conn->errorInfo()[2]);
    }

    $stmt->execute([
        $id,
        $patient_name,
        $temperature,
        $ecg_rate,
        $pulse_rate,
        $spo2_level,
        $blood_pressure,
        $diagnosis
    ]);

    echo json_encode(['success' => true, 'message' => 'Readings and diagnosis stored successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    Database::disconnect(); // Ensure the connection is closed
}
?>
