<?php
include 'database.php';

$response = ['exists' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uid'])) {
    $uid = trim($_POST['uid']);

    try {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM health_diagnostics WHERE id = ?");
        $stmt->execute([$uid]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $response['exists'] = true;
        }

        Database::disconnect();
    } catch (Exception $e) {
        error_log("Error checking UID: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
