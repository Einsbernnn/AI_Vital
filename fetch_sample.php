<?php
header('Content-Type: application/json');

echo json_encode([
    "body_temp" => 44,
    "ecg" => 90,
    "pulse_rate" => 95,
    "spo2" => 90,
    "bp" => "140/90", // add mmHG when sampling
]);
?>