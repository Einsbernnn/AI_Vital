<?php
header('Content-Type: application/json');

// Randomize data every request (except BP)
echo json_encode([
    "body_temp" => rand(310, 370) / 10, // 31.0°C to 37.0°C
    "ecg" => rand(60, 120),             // ECG beats per minute
    "pulse_rate" => rand(60, 120),      // Pulse rate BPM
    "spo2" => rand(88, 100),            // Oxygen saturation %                 // BP stays constant
]);
?>