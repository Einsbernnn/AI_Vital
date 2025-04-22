<?php
header('Content-Type: application/json');

echo json_encode([
    "body_temp" => 38.0,
    "ecg" => 90,
    "pulse_rate" => 90,
    "spo2" => 100,
    "bp" => "140/90"
]);
?>