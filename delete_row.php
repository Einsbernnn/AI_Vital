<?php
include 'database.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['count'])) {
    $count = intval($_POST['count']);

    try {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM health_readings WHERE count = ?");
        $stmt->execute([$count]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
        }

        Database::disconnect();
    } catch (Exception $e) {
        error_log("Error deleting row: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
