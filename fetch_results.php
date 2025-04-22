<?php
header('Content-Type: application/json');

if (!isset($_GET['uid'])) {
    echo json_encode([]);
    exit();
}

include 'database.php';
$pdo = Database::connect();

$uid = $_GET['uid'];
$stmt = $pdo->prepare("SELECT * FROM health_readings WHERE id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

Database::disconnect();

echo json_encode($readings);
