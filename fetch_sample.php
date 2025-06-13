<?php
header('Content-Type: application/json');

echo json_encode([
    "body_temp" => 0,
    "ecg" => 0,
    "pulse_rate" => 0,
    "spo2" => 0
]);
?>