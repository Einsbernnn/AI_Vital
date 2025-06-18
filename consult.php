<?php
// Enable error reporting at the start of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Add a verification message
file_put_contents(__DIR__ . '/consult_access.log', date('Y-m-d H:i:s') . " - File accessed\n", FILE_APPEND);

// Include required files
require_once __DIR__ . '/database.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure session is started for access to $_SESSION variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize logging
$logFile = __DIR__ . '/consult_debug.log';
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Script started\n", FILE_APPEND);
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Request URI: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Query String: " . $_SERVER['QUERY_STRING'] . "\n", FILE_APPEND);

// Log raw input
$rawInput = file_get_contents('php://input');
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Raw Input: " . $rawInput . "\n", FILE_APPEND);

// Log GET and POST data
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] GET Data: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Handle GET request for sensor_summary
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sensor_summary'])) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Received GET request with sensor_summary\n", FILE_APPEND);
    
    try {
        // Decode the URL-encoded summary
        $decoded_summary = urldecode($_GET['sensor_summary']);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Decoded summary: " . $decoded_summary . "\n", FILE_APPEND);
        
        // Set the decoded summary in POST array for processing
        $_POST['sensor_summary'] = $decoded_summary;
        $consultInput = "Please analyze these vital signs:\n" . $decoded_summary;
        
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Successfully processed GET request\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error processing GET request: " . $e->getMessage() . "\n", FILE_APPEND);
        error_log("Error processing GET request: " . $e->getMessage());
        header("Location: live reading.php?error=processing_error");
        exit();
    }
}

// Check for required sensor_summary data
if (!isset($_POST['sensor_summary'])) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error: No sensor_summary data found\n", FILE_APPEND);
    header("Location: live reading.php?error=no_data");
    exit();
}

// Process the sensor summary
$sensor_summary = $_POST['sensor_summary'];
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Processing sensor summary:\n" . $sensor_summary . "\n", FILE_APPEND);

// Extract vital signs
$temperature = null;
$ecg_rate = null;
$pulse_rate = null;
$spo2_level = null;
$blood_pressure = null;

// Extract temperature
if (preg_match('/Temp\s*:\s*([\d.]+)\s*°C/i', $sensor_summary, $matches)) {
    $temperature = floatval($matches[1]);
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted temperature: $temperature\n", FILE_APPEND);
}

// Extract ECG rate
if (preg_match('/ECG\s*:\s*([\d.]+)/i', $sensor_summary, $matches)) {
    $ecg_rate = floatval($matches[1]);
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted ECG rate: $ecg_rate\n", FILE_APPEND);
}

// Extract pulse rate
if (preg_match('/Pulse\s*:\s*([\d.]+)\s*BPM/i', $sensor_summary, $matches)) {
    $pulse_rate = floatval($matches[1]);
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted pulse rate: $pulse_rate\n", FILE_APPEND);
}

// Extract SpO2 level
if (preg_match('/SpO2\s*:\s*([\d.]+)\s*%/i', $sensor_summary, $matches)) {
    $spo2_level = floatval($matches[1]);
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted SpO2 level: $spo2_level\n", FILE_APPEND);
}

// Extract blood pressure
if (preg_match('/BP\s*:\s*([\d\/]+)/i', $sensor_summary, $matches)) {
    $blood_pressure = trim($matches[1]);
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted blood pressure: $blood_pressure\n", FILE_APPEND);
}

// Validate extracted values
if ($temperature === null || $ecg_rate === null || $pulse_rate === null || $spo2_level === null || $blood_pressure === null) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error: Failed to extract all vital signs\n", FILE_APPEND);
    header("Location: live reading.php?error=invalid_data");
    exit();
}

// Log any PHP errors
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logFile) {
    $error_message = "[" . date('Y-m-d H:i:s') . "] PHP Error [$errno]: $errstr in $errfile on line $errline\n";
    file_put_contents($logFile, $error_message, FILE_APPEND);
    error_log($error_message);
    return false;
});

// Log any uncaught exceptions
set_exception_handler(function($exception) use ($logFile) {
    $error_message = "[" . date('Y-m-d H:i:s') . "] Uncaught Exception: " . $exception->getMessage() . "\n";
    $error_message .= "Stack trace: " . $exception->getTraceAsString() . "\n";
    file_put_contents($logFile, $error_message, FILE_APPEND);
    error_log($error_message);
});

// Get current date for fallback purposes
$currentDate = date("F j, Y");

// Initialize AI responses array
$ai_responses = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['sensor_summary'])) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Processing request with sensor data\n", FILE_APPEND);
    $userInput = isset($_POST['consultInput']) ? trim($_POST['consultInput']) : "Please analyze these vital signs:\n" . urldecode($_GET['sensor_summary']);

    // Get user details
    $uid = isset($_POST['uid']) ? trim($_POST['uid']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $age = isset($_POST['age']) ? trim($_POST['age']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';

    // Parse sensor values from the sensor_summary
    $temperature = 0.00;
    $ecg_rate = 0.00;
    $pulse_rate = 0.00;
    $spo2_level = 0.00;
    $blood_pressure = '';

    $sensor_summary = isset($_POST['sensor_summary']) ? $_POST['sensor_summary'] : $_GET['sensor_summary'];
    
    // Log the raw sensor summary for debugging
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Raw sensor summary:\n" . $sensor_summary . "\n", FILE_APPEND);
    
    // Extract temperature
    if (preg_match('/Temp:\s*([\d.]+)\s*°C/i', $sensor_summary, $matches)) {
        $temperature = floatval($matches[1]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted temperature: $temperature\n", FILE_APPEND);
    }
    
    // Extract ECG rate
    if (preg_match('/ECG:\s*([\d.]+)/i', $sensor_summary, $matches)) {
        $ecg_rate = floatval($matches[1]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted ECG rate: $ecg_rate\n", FILE_APPEND);
    }
    
    // Extract pulse rate
    if (preg_match('/Pulse:\s*([\d.]+)\s*BPM/i', $sensor_summary, $matches)) {
        $pulse_rate = floatval($matches[1]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted pulse rate: $pulse_rate\n", FILE_APPEND);
    }
    
    // Extract SpO2 level - try multiple patterns to handle different formats
    if (preg_match('/SpO2:\s*([\d.]+)\s*%/i', $sensor_summary, $matches) || 
        preg_match('/SpO₂:\s*([\d.]+)\s*%/i', $sensor_summary, $matches) ||
        preg_match('/SpO2\s*:\s*([\d.]+)/i', $sensor_summary, $matches)) {
        $spo2_level = floatval($matches[1]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted SpO2 level: $spo2_level\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Failed to extract SpO2 level. Raw summary: $sensor_summary\n", FILE_APPEND);
    }
    
    // Extract blood pressure
    if (preg_match('/BP:\s*([\d\/]+)/i', $sensor_summary, $matches)) {
        $blood_pressure = trim($matches[1]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Extracted blood pressure: $blood_pressure\n", FILE_APPEND);
    }

    // Log all extracted values
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] All extracted values:\n", FILE_APPEND);
    file_put_contents($logFile, "Temperature: $temperature\n", FILE_APPEND);
    file_put_contents($logFile, "ECG Rate: $ecg_rate\n", FILE_APPEND);
    file_put_contents($logFile, "Pulse Rate: $pulse_rate\n", FILE_APPEND);
    file_put_contents($logFile, "SpO2 Level: $spo2_level\n", FILE_APPEND);
    file_put_contents($logFile, "Blood Pressure: $blood_pressure\n", FILE_APPEND);

    // Validate numeric values
    $temperature = is_numeric($temperature) ? floatval($temperature) : 0.00;
    $ecg_rate = is_numeric($ecg_rate) ? floatval($ecg_rate) : 0.00;
    $pulse_rate = is_numeric($pulse_rate) ? floatval($pulse_rate) : 0.00;
    $spo2_level = is_numeric($spo2_level) ? floatval($spo2_level) : 0.00;

    // Log the validated values
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Validated values:\n", FILE_APPEND);
    file_put_contents($logFile, "Temperature: $temperature\n", FILE_APPEND);
    file_put_contents($logFile, "ECG Rate: $ecg_rate\n", FILE_APPEND);
    file_put_contents($logFile, "Pulse Rate: $pulse_rate\n", FILE_APPEND);
    file_put_contents($logFile, "SpO2 Level: $spo2_level\n", FILE_APPEND);
    file_put_contents($logFile, "Blood Pressure: $blood_pressure\n", FILE_APPEND);

    // If sensor_summary is present in POST, append it to $userInput if not already present
    if (isset($_POST['sensor_summary']) && $_POST['sensor_summary']) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Sensor summary found in POST\n", FILE_APPEND);
        $sensor_summary = trim($_POST['sensor_summary']);
        if (strpos($userInput, $sensor_summary) === false) {
            $userInput = trim($userInput . "\n\n" . $sensor_summary);
        }
    }

    $prompts = [
        "Patient vital signs received:\n\n$userInput\n\nPlease analyze the values and provide a medically accurate interpretation. Identify any abnormalities (e.g., hypothermia, tachycardia, low SpO₂), explain the clinical implications, and recommend appropriate actions. Consider standard reference ranges for adults. Maintain a professional and empathetic tone. Conclude with guidance on whether to seek immediate medical attention or continue monitoring."
    ];

    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Sending request to Cohere API\n", FILE_APPEND);
    $cohere_url = 'https://api.cohere.com/v2/chat';
    $api_key = 'F3LM9ycUnenzInMB2m94RWdRwHuQLnTH7cT2f5qB';
    $model = 'command-a-03-2025';

    foreach ($prompts as $prompt) {
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $ch = curl_init($cohere_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
            'Authorization: bearer ' . $api_key
        ]);

        $result = curl_exec($ch);
        if ($result === false) {
            $ai_responses[] = "Error: " . curl_error($ch);
        } else {
            $data = json_decode($result, true);
            // Handle Cohere's nested response
            if (isset($data['text'])) {
                $ai_responses[] = $data['text'];
            } elseif (isset($data['reply'])) {
                $ai_responses[] = $data['reply'];
            } elseif (isset($data['message']['content'][0]['text'])) {
                $ai_responses[] = $data['message']['content'][0]['text'];
            } elseif (isset($data['content'][0]['text'])) {
                $ai_responses[] = $data['content'][0]['text'];
            } elseif (isset($data['message'])) {
                $msg = is_array($data['message']) ? json_encode($data['message']) : $data['message'];
                $ai_responses[] = "API Error: " . $msg;
            } elseif (isset($data['error'])) {
                $err = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
                $ai_responses[] = "API Error: " . $err;
            } else {
                $ai_responses[] = "No response from AI. Raw: " . $result;
            }
        }
        curl_close($ch);
    }

    // Convert AI responses to a single string
    $ai_result = '';
    if (is_array($ai_responses)) {
        $ai_result = implode("\n\n", array_map(function($response) {
            return trim($response);
        }, $ai_responses));
    } else {
        $ai_result = trim($ai_responses);
    }

    // Store to health_readings table
    try {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO health_readings (id, patient_name, temperature, ecg_rate, pulse_rate, spo2_level, blood_pressure, diagnosis, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // Log the SQL parameters for debugging
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] SQL Parameters:\n", FILE_APPEND);
        file_put_contents($logFile, "UID: $uid\n", FILE_APPEND);
        file_put_contents($logFile, "Name: $name\n", FILE_APPEND);
        file_put_contents($logFile, "Temperature: $temperature\n", FILE_APPEND);
        file_put_contents($logFile, "ECG Rate: $ecg_rate\n", FILE_APPEND);
        file_put_contents($logFile, "Pulse Rate: $pulse_rate\n", FILE_APPEND);
        file_put_contents($logFile, "SpO2 Level: $spo2_level\n", FILE_APPEND);
        file_put_contents($logFile, "Blood Pressure: $blood_pressure\n", FILE_APPEND);
        file_put_contents($logFile, "Diagnosis length: " . strlen($ai_result) . "\n", FILE_APPEND);

        $stmt->execute([
            $uid,
            $name,
            $temperature,
            $ecg_rate,
            $pulse_rate,
            $spo2_level,
            $blood_pressure,
            $ai_result
        ]);
        Database::disconnect();
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Successfully stored AI result and sensor values to health_readings table for UID: $uid\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error storing to health_readings: " . $e->getMessage() . "\n", FILE_APPEND);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    }
}

file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ===== Script Ended =====\n", FILE_APPEND);

// After AI response is received, send email using PHPMailer
if (!empty($ai_responses)) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AI responses received, preparing to send email\n", FILE_APPEND);
    
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'einsbernsystem@gmail.com';
        $mail->Password = 'bdov zsdz sidj bcsc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('einsbernsystem@gmail.com', 'AI-Vital Diagnoser');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Health Diagnostic Results from AI-Vital";

        // Prepare email body
        $emailBody = "
            <h3>Hello $name,</h3>
            <p>Here are your health readings:</p>
            <ul>
                <li><strong>Body Temperature:</strong> $temperature °C</li>
                <li><strong>ECG Rate:</strong> $ecg_rate BPM</li>
                <li><strong>Pulse Rate:</strong> $pulse_rate BPM</li>
                <li><strong>SpO₂ Level:</strong> $spo2_level %</li>
                <li><strong>Blood Pressure:</strong> $blood_pressure mmHg</li>
            </ul>
            <p><strong>AI Diagnosis:</strong></p>
            <p style='white-space: pre-line;'>$ai_result</p>
            <p>Stay healthy and take care!</p>
        ";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        // Send email
        $mail->send();
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Email sent successfully to $email\n", FILE_APPEND);
        
        // Store email status in session
        $_SESSION['email_sent'] = true;
        $_SESSION['email_message'] = "The diagnostic result has been sent to \"$email\" successfully.";

        // Add success notification for email
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Sent Successfully',
                    text: 'Your diagnostic results have been sent to your email address.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            });
        </script>";

    } catch (Exception $e) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Email sending failed: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        $_SESSION['email_sent'] = false;
        $_SESSION['email_message'] = "Failed to send email. Please try again later.";

        // Add error notification for email
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Email Sending Failed',
                    text: 'Failed to send email. Please try again later.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#d33',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            });
        </script>";
    }

    // Store to database
    try {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO health_readings (id, patient_name, temperature, ecg_rate, pulse_rate, spo2_level, blood_pressure, diagnosis, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([
            $uid,
            $name,
            $temperature,
            $ecg_rate,
            $pulse_rate,
            $spo2_level,
            $blood_pressure,
            $ai_result
        ]);
        Database::disconnect();
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Successfully stored AI result and sensor values to health_readings table for UID: $uid\n", FILE_APPEND);

        // Add success notification for database storage
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Data Stored Successfully',
                    text: 'Your diagnostic results have been saved to the database.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            });
        </script>";

    } catch (Exception $e) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error storing to health_readings: " . $e->getMessage() . "\n", FILE_APPEND);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Error trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);

        // Add error notification for database storage
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Database Storage Failed',
                    text: 'Failed to store your diagnostic results. Please try again later.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#d33',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            });
        </script>";
    }
}

// Pre-fill textarea with sensor summary if provided via GET
$sensor_summary = isset($_GET['sensor_summary']) ? $_GET['sensor_summary'] : '';

// Initialize sensor_data array
$sensor_data = [];
if (!empty($sensor_summary)) {
    $sensor_data = explode("\n", trim($sensor_summary));
}

// Define questions for each sensor type
$sensor_questions = [
    'Temp' => [
        'high' => [
            'Are you experiencing any fever or chills?',
            'Do you feel unusually hot or sweaty?',
            'Have you been exposed to hot environments recently?',
            'Are you experiencing any fatigue or weakness?',
            'Have you been taking any medications that might affect body temperature?'
        ],
        'low' => [
            'Are you feeling unusually cold?',
            'Have you been exposed to cold environments?',
            'Are you experiencing any shivering?',
            'Do you feel more tired than usual?',
            'Have you been taking any medications that might affect body temperature?'
        ]
    ],
    'ECG' => [
        'high' => [
            'Are you experiencing any chest pain or discomfort?',
            'Do you feel your heart racing or beating irregularly?',
            'Are you experiencing any shortness of breath?',
            'Do you feel dizzy or lightheaded?',
            'Have you been under significant stress recently?'
        ],
        'low' => [
            'Are you feeling unusually tired or fatigued?',
            'Do you feel dizzy or lightheaded?',
            'Have you been exercising recently?',
            'Are you taking any medications that might affect heart rate?',
            'Do you feel weak or have difficulty with physical activity?'
        ]
    ],
    'Pulse' => [
        'high' => [
            'Are you experiencing any chest pain?',
            'Do you feel your heart racing?',
            'Are you feeling anxious or stressed?',
            'Have you been physically active recently?',
            'Are you experiencing any shortness of breath?'
        ],
        'low' => [
            'Are you feeling unusually tired?',
            'Do you feel dizzy or lightheaded?',
            'Have you been resting or inactive?',
            'Are you taking any medications?',
            'Do you feel weak or have low energy?'
        ]
    ],
    'SpO₂' => [
        'high' => [
            'Are you breathing normally?',
            'Do you feel any chest tightness?',
            'Are you experiencing any shortness of breath?',
            'Have you been at high altitudes recently?',
            'Are you taking any respiratory medications?'
        ],
        'low' => [
            'Are you experiencing any difficulty breathing?',
            'Do you feel short of breath?',
            'Are you feeling tired or fatigued?',
            'Have you been coughing or wheezing?',
            'Do you have any respiratory conditions?'
        ]
    ],
    'BP' => [
        'high' => [
            'Are you experiencing any headaches?',
            'Do you feel any chest pain?',
            'Are you feeling dizzy or lightheaded?',
            'Have you been under stress recently?',
            'Are you taking any blood pressure medications?'
        ],
        'low' => [
            'Are you feeling dizzy or lightheaded?',
            'Do you feel weak or fatigued?',
            'Have you been standing for long periods?',
            'Are you taking any medications?',
            'Do you feel nauseous or have blurred vision?'
        ]
    ]
];

// Track if we have any abnormal readings
$has_abnormal_readings = false;
$abnormal_sensors = [];

// First pass: identify abnormal readings
for ($i = 0; $i < count($sensor_data); $i += 2) {
    if (isset($sensor_data[$i]) && isset($sensor_data[$i + 1])) {
        $type = trim($sensor_data[$i]);
        $value = trim($sensor_data[$i + 1]);
        
        // Calculate severity
        $severity = 'normal';
        $numeric_value = 0;
        
        switch($type) {
            case 'Temp':
                $numeric_value = floatval(str_replace('°C', '', $value));
                if ($numeric_value < 36.1) $severity = 'low';
                else if ($numeric_value > 37.2) $severity = 'high';
                break;
            case 'ECG':
                $numeric_value = floatval($value);
                if ($numeric_value < 60) $severity = 'low';
                else if ($numeric_value > 100) $severity = 'high';
                break;
            case 'Pulse':
                $numeric_value = floatval(str_replace('BPM', '', $value));
                if ($numeric_value < 60) $severity = 'low';
                else if ($numeric_value > 100) $severity = 'high';
                break;
            case 'SpO₂':
                $numeric_value = floatval(str_replace('%', '', $value));
                if ($numeric_value < 95) $severity = 'low';
                else if ($numeric_value > 100) $severity = 'high';
                break;
            case 'BP':
                $bp_parts = explode('/', str_replace('mmHg', '', $value));
                if (count($bp_parts) === 2) {
                    $systolic = floatval($bp_parts[0]);
                    $diastolic = floatval($bp_parts[1]);
                    if ($systolic < 90 || $diastolic < 60) $severity = 'low';
                    else if ($systolic > 120 || $diastolic > 80) $severity = 'high';
                }
                break;
        }

        if ($severity !== 'normal') {
            $has_abnormal_readings = true;
            $abnormal_sensors[] = [
                'type' => $type,
                'value' => $value,
                'severity' => $severity
            ];
        }
    }
}

// Get BP values from session storage (they might be NaN or empty)
$systolic = isset($_SESSION['systolic']) ? $_SESSION['systolic'] : 'N/A';
$diastolic = isset($_SESSION['diastolic']) ? $_SESSION['diastolic'] : 'N/A';

// Function to check if BP values are valid
function isValidBP($systolic, $diastolic) {
    return ($systolic !== 'NaN' && $systolic !== 'N/A' && 
            $diastolic !== 'NaN' && $diastolic !== 'N/A' &&
            is_numeric($systolic) && is_numeric($diastolic));
}

// Get other required data
$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : '';
$name = isset($_SESSION['name']) ? $_SESSION['name'] : '';
$age = isset($_SESSION['age']) ? $_SESSION['age'] : '';
$gender = isset($_SESSION['gender']) ? $_SESSION['gender'] : '';

// Check if required fields are present
if (empty($uid) || empty($name) || empty($age) || empty($gender)) {
    // Redirect back if required fields are missing
    header("Location: live reading.php");
    exit();
}

// Prepare data for consultation
$data = array(
    'uid' => $uid,
    'name' => $name,
    'age' => $age,
    'gender' => $gender,
    'systolic' => $systolic,
    'diastolic' => $diastolic,
    'bp_status' => isValidBP($systolic, $diastolic) ? 'valid' : 'not_available'
);

// Store data in session for use in consultation page
$_SESSION['consultation_data'] = $data;

// Proceed to consultation page
header("Location: consultation.php");
exit();
?>