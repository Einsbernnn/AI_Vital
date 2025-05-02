<?php
$esp32IP = "http://172.20.10.3"; // ESP32 IP Address

// Send a GET request to the ESP32 to notify button press
$ch = curl_init("$esp32IP/button"); // Change '/button' to your ESP32 endpoint
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Optionally, return the ESP32's response
echo $response ? $response : "Signal sent to ESP32.";
?>