<?php
include 'database.php';

$uid = $_GET['uid'] ?? '';
$response = [];

if (!empty($uid)) {
    $pdo = Database::connect();
    error_log("Received UID: " . $uid);

    // Query to check if UID exists in health_diagnostics
    $stmt = $pdo->prepare("SELECT * FROM health_diagnostics WHERE id = ?");
    $stmt->execute([$uid]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // User found - return the data
        $response = $result;
    } else {
        // User not found - explicit error response
        $response = [
            'error' => 'no_match',
            'message' => 'This RFID card is not registered in the system.'
        ];
    }

    Database::disconnect();
} else {
    $response = [
        'error' => 'invalid_input',
        'message' => 'No UID provided'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
