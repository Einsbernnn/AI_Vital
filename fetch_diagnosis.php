<?php
require 'database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception("ID is required.");
    }

    $id = $_GET['id'];

    // Establish database connection
    $conn = Database::connect();

    // Fetch the diagnosis from the health_readings table
    $query = "SELECT diagnosis FROM health_readings WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->errorInfo()[2]);
    }

    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("No diagnosis found for the given ID.");
    }

    echo json_encode(['success' => true, 'diagnosis' => $result['diagnosis']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    Database::disconnect();
}
?>
