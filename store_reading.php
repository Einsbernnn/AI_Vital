<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'database.php';

    $data = json_decode(file_get_contents("php://input"), true);

    $uid = $data['uid'] ?? null;
    $name = $data['name'] ?? null;
    $gender = $data['gender'] ?? null;
    $age = $data['age'] ?? null;
    $height = $data['height'] ?? null;
    $weight = $data['weight'] ?? null;
    $bodyTemp = $data['body_temperature'] ?? null;
    $ecg = $data['ecg'] ?? null;
    $pulseRate = $data['pulse_rate'] ?? null;
    $spo2 = $data['spo2'] ?? null;
    $bp = $data['blood_pressure'] ?? null;

    if (!$uid || !$name || !$gender || !$age || !$height || !$weight || !$bodyTemp || !$ecg || !$pulseRate || !$spo2 || !$bp) {
        echo json_encode(["success" => false, "message" => "Invalid input data."]);
        exit;
    }

    try {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO health_data (id, name, gender, age, height, weight, body_temperature, ecg, pulse_rate, spo2, blood_pressure, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$uid, $name, $gender, $age, $height, $weight, $bodyTemp, $ecg, $pulseRate, $spo2, $bp]);
        Database::disconnect();

        echo json_encode(["success" => true, "message" => "Reading stored successfully."]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
