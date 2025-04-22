<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['clerk1_logged_in']) && !isset($_SESSION['clerk2_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

include 'database.php';

$pdo = Database::connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit();
}

$uid = $data['uid'] ?? '';
$name = $data['name'] ?? '';
$age = $data['age'] ?? '';
$weight = $data['weight'] ?? '';
$height = $data['height'] ?? '';
$gender = $data['gender'] ?? '';
$temp = isset($data['body_temperature']) && is_numeric($data['body_temperature']) ? floatval($data['body_temperature']) : 0;
$bp = $data['blood_pressure'] ?? 'N/A';
$ecg = isset($data['ecg']) && is_numeric($data['ecg']) ? floatval($data['ecg']) : 0;
$pulse_rate = isset($data['pulse_rate']) && is_numeric($data['pulse_rate']) ? floatval($data['pulse_rate']) : 0;
$spo2 = isset($data['spo2']) && is_numeric($data['spo2']) ? floatval($data['spo2']) : 0;

$sql = "INSERT INTO health_data (id, name, gender, age, height, weight, body_temperature, blood_pressure, ecg, pulse_rate, spo2, timestamp)
        VALUES (:uid, :name, :gender, :age, :height, :weight, :body_temperature, :blood_pressure, :ecg, :pulse_rate, :spo2, NOW())";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ':uid' => $uid,
        ':name' => $name,
        ':gender' => $gender,
        ':age' => $age,
        ':height' => $height,
        ':weight' => $weight,
        ':body_temperature' => $temp,
        ':blood_pressure' => $bp,
        ':ecg' => $ecg,
        ':pulse_rate' => $pulse_rate,
        ':spo2' => $spo2
    ]);

    echo json_encode(['success' => true, 'message' => 'Data registered successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

Database::disconnect();
?>
