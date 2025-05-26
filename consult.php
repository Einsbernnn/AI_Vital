<?php
// Enable error reporting at the start of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Include required files
require_once __DIR__ . '/database.php';

// Ensure session is started for access to $_SESSION variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize logging
$logFile = __DIR__ . '/mail_debug.log';
$timestamp = date('Y-m-d H:i:s');

// Check if log file is writable
if (!is_writable($logFile) && !is_writable(dirname($logFile))) {
    error_log("Mail debug log file is not writable: $logFile");
}

// Clear the log file at the start of each request
file_put_contents($logFile, "[$timestamp] ===== Script Started =====\n");
file_put_contents($logFile, "[$timestamp] PHP Version: " . PHP_VERSION . "\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

// Log POST data if present
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents($logFile, "[$timestamp] POST data received: " . json_encode($_POST) . "\n", FILE_APPEND);
}

// Log any PHP errors
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logFile, $timestamp) {
    $error_message = "[$timestamp] PHP Error [$errno]: $errstr in $errfile on line $errline\n";
    file_put_contents($logFile, $error_message, FILE_APPEND);
    error_log($error_message);
    return false;
});

// Log any uncaught exceptions
set_exception_handler(function($exception) use ($logFile, $timestamp) {
    $error_message = "[$timestamp] Uncaught Exception: " . $exception->getMessage() . "\n";
    $error_message .= "Stack trace: " . $exception->getTraceAsString() . "\n";
    file_put_contents($logFile, $error_message, FILE_APPEND);
    error_log($error_message);
});

// Get current date for fallback purposes
$currentDate = date("F j, Y");

// Initialize AI responses array
$ai_responses = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultInput'])) {
    file_put_contents($logFile, "[$timestamp] POST request received with consultInput\n", FILE_APPEND);
    $userInput = trim($_POST['consultInput']);

    // If sensor_summary is present in POST, append it to $userInput if not already present
    if (isset($_POST['sensor_summary']) && $_POST['sensor_summary']) {
        file_put_contents($logFile, "[$timestamp] Sensor summary found in POST\n", FILE_APPEND);
        $sensor_summary = trim($_POST['sensor_summary']);
        if (strpos($userInput, $sensor_summary) === false) {
            $userInput = trim($userInput . "\n\n" . $sensor_summary);
        }
    }

    $prompts = [
        "Patient vital signs received:\n\n$userInput\n\nPlease analyze the values and provide a medically accurate interpretation. Identify any abnormalities (e.g., hypothermia, tachycardia, low SpO₂), explain the clinical implications, and recommend appropriate actions. Consider standard reference ranges for adults. Maintain a professional and empathetic tone. Conclude with guidance on whether to seek immediate medical attention or continue monitoring."
    ];

    file_put_contents($logFile, "[$timestamp] Sending request to Cohere API\n", FILE_APPEND);
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
}

file_put_contents($logFile, "[$timestamp] ===== Script Ended =====\n", FILE_APPEND);

// After AI response is received, send email using PHPMailer
if (!empty($ai_responses)) {
    file_put_contents($logFile, "[$timestamp] AI responses received, preparing to send email\n", FILE_APPEND);
    file_put_contents($logFile, "[$timestamp] AI Response content: " . json_encode($ai_responses) . "\n", FILE_APPEND);
    
    // --- BEGIN ANTI-SPAM LOGIC ---
    $uid = '';
    // Try to get UID from POST, GET, or session
    if (isset($_POST['uid'])) {
        $uid = trim($_POST['uid']);
        file_put_contents($logFile, "[$timestamp] UID found in POST: $uid\n", FILE_APPEND);
    } elseif (isset($_GET['uid'])) {
        $uid = trim($_GET['uid']);
        file_put_contents($logFile, "[$timestamp] UID found in GET: $uid\n", FILE_APPEND);
    } elseif (isset($_SESSION['uid'])) {
        $uid = trim($_SESSION['uid']);
        file_put_contents($logFile, "[$timestamp] UID found in SESSION: $uid\n", FILE_APPEND);
    } else {
        // Try to get UID from UIDContainer.php
        $uid = trim(file_get_contents('UIDContainer.php'));
        if ($uid) {
            file_put_contents($logFile, "[$timestamp] UID found in UIDContainer: $uid\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "[$timestamp] UID not found\n", FILE_APPEND);
            // If no UID is found, we should not proceed with the insert
            return;
        }
    }

    // Get user details from handle_rfid.php using the UID
    $user_details = [];
    try {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT name FROM userdata WHERE id = ?");
        $stmt->execute([$uid]);
        $user_details = $stmt->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
    } catch (Exception $e) {
        file_put_contents($logFile, "[$timestamp] Error fetching user details: " . $e->getMessage() . "\n", FILE_APPEND);
    }

    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : ($user_details['name'] ?? '');
    $age = isset($_POST['age']) ? $_POST['age'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $sensor_summary = isset($_POST['sensor_summary']) ? $_POST['sensor_summary'] : (isset($_GET['sensor_summary']) ? $_GET['sensor_summary'] : '');
    // Convert AI responses to a single longtext string
    $ai_result = '';
    if (is_array($ai_responses)) {
        $ai_result = implode("\n\n", array_map(function($response) {
            return trim($response);
        }, $ai_responses));
    } else {
        $ai_result = trim($ai_responses);
    }
    $created_at = date('Y-m-d H:i:s');

    // Extract sensor values from sensor_summary
    $temperature = '';
    $ecg_rate = '';
    $pulse_rate = '';
    $spo2_level = '';
    $blood_pressure = '';

    if ($sensor_summary) {
        // Extract values using regex patterns
        if (preg_match('/Temperature:\s*([\d.]+)/i', $sensor_summary, $matches)) {
            $temperature = $matches[1];
        }
        if (preg_match('/ECG Rate:\s*([\d.]+)/i', $sensor_summary, $matches)) {
            $ecg_rate = $matches[1];
        }
        if (preg_match('/Pulse Rate:\s*([\d.]+)/i', $sensor_summary, $matches)) {
            $pulse_rate = $matches[1];
        }
        if (preg_match('/SpO2 Level:\s*([\d.]+)/i', $sensor_summary, $matches)) {
            $spo2_level = $matches[1];
        }
        if (preg_match('/Blood Pressure:\s*([\d.]+)/i', $sensor_summary, $matches)) {
            $blood_pressure = $matches[1];
        }
    }

    // Store to health_consult table
    try {
        $response = file_get_contents('store_consultation.php', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode([
                    'uid' => $uid,
                    'name' => $name,
                    'temperature' => $temperature,
                    'ecg_rate' => $ecg_rate,
                    'pulse_rate' => $pulse_rate,
                    'spo2_level' => $spo2_level,
                    'blood_pressure' => $blood_pressure,
                    'consultation' => $ai_result
                ])
            ]
        ]));

        $result = json_decode($response, true);
        if ($result && $result['success']) {
            file_put_contents($logFile, "[$timestamp] Stored AI result and sensor values to health_consult table for UID: $uid\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "[$timestamp] Error storing to health_consult: " . ($result['message'] ?? 'Unknown error') . "\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        file_put_contents($logFile, "[$timestamp] Error storing to health_consult: " . $e->getMessage() . "\n", FILE_APPEND);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Vital: Live Readings</title>

    <!-- Favicons -->
    <link href="img/logo.png" rel="icon">
    <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&family=Marcellus:wght@400&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="vendor/aos/aos.css" rel="stylesheet">
    <link href="vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="css/main.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        fadeIn: 'fadeIn 1.5s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    },
                },
            },
        };
    </script>
    <style>
        body {
            background: url('microcity.jpg') no-repeat center center fixed;
            background-size: cover;
            margin-bottom: 50px;
        }
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.8);
        }
        /* Nav menu as links only, no button style, always visible and horizontal, scrollable if needed */
        #navmenu {
            background: none;
            box-shadow: none;
        }
        #navmenu ul {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
            align-items: center;
            overflow-x: auto;
            white-space: nowrap;
            width: 100%;
        }
        #navmenu ul li {
            display: block;
        }
        #navmenu ul li a {
            display: block;
            padding: 8px 18px;
            color: #222;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            background: none;
            border: none;
            transition: background 0.2s, color 0.2s;
        }
        #navmenu ul li a:hover,
        #navmenu ul li a.active {
            background: #22c55e;
            color: #fff !important;
        }
        .mobile-nav-toggle, .bi-list {
            display: none !important;
        }
        /* Responsive: shrink gap and font size on small screens, but always show nav */
        @media (max-width: 600px) {
            #navmenu ul {
                gap: 0.5rem;
                font-size: 0.95rem;
            }
            #navmenu ul li a {
                padding: 6px 10px;
                font-size: 0.95rem;
            }
        }
        .grid-item {
            background: #f0fdf4; /* Light green background */
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 2px solid #d1fae5; /* Green border */
        }
        .grid-item p {
            margin: 0;
        }
        .grid-item .label {
            font-size: 24px;
            font-weight: bold;
            color: #065f46; /* Dark green text */
        }
        .grid-item .value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 6px;
        }
        .grid-item.red { border-color: #EF4444; }
        .grid-item.red .value { color: #EF4444; }
        .grid-item.yellow { border-color: #F59E0B; }
        .grid-item.yellow .value { color: #F59E0B; }
        .grid-item.green { border-color: #10B981; }
        .grid-item.green .value { color: #10B981; }
        .grid-item.blue { border-color: #3B82F6; }
        .grid-item.blue .value { color: #3B82F6; }
        .grid-item.purple { border-color: #8B5CF6; }
        .grid-item.purple .value { color: #8B5CF6; }
        .user-panel, .readings-panel {
            background: #ffffff;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .user-panel h3, .readings-panel h3 {
            font-size: 40px;
            text-align: center;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 19px;
        }
        .user-panel table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-panel table td {
            padding: 8px;
            border-bottom: 1px solid #d1fae5;
            font-size: 14px;
            color: #065f46;
        }
        .user-panel table td:first-child {
            font-weight: bold;
            color: #064e3b;
        }
        .marquee {
            background: #065f46;
            color: white;
            padding: 10px 0;
            font-size: 16px;
            font-weight: bold;
            overflow: hidden;
            white-space: nowrap;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }
        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 30s linear infinite;
        }
        @keyframes marquee {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }
        footer {
            margin-bottom: 60px;
        }
        @keyframes scrollLeft {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(-100%);
            }
        }
        .sensor-summary-compact {
            min-width: 160px;
            max-width: 220px;
            background: #f0fdf4;
            border: 2px solid #22c55e;
            border-radius: 8px;
            color: #065f46;
            font-size: 0.98rem;
            padding: 10px 14px;
            margin-right: 18px;
            margin-bottom: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            height: 100%;
        }
        .sensor-summary-compact .title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 1.05rem;
        }
        .consult-form-flex {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            width: 100%;
        }
        .sensor-summary-compact {
            /* Make the summary box stretch to match the textarea height */
            align-self: stretch;
        }
        .consult-input-col {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        @media (max-width: 900px) {
            .sensor-summary-compact {
                min-width: 120px;
                max-width: 99vw;
                font-size: 0.93rem;
                margin-right: 8px;
            }
        }
        @media (max-width: 600px) {
            .consult-form-flex {
                flex-direction: column;
                align-items: stretch;
            }
            .sensor-summary-compact {
                min-width: 0;
                max-width: 100vw;
                font-size: 0.91rem;
                margin-right: 0;
                margin-bottom: 10px;
                align-self: auto;
            }
        }
        .sensor-summary-horizontal {
            background: #f0fdf4;
            border: 2px solid #22c55e;
            border-radius: 8px;
            color: #065f46;
            font-size: 0.98rem;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            width: 100%;
        }
        .sensor-summary-horizontal .title {
            font-weight: bold;
            font-size: 1.2rem;
            color: #065f46;
            margin-bottom: 10px;
        }
        .sensor-summary-horizontal pre {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: flex-start;
            align-items: center;
        }
        @media (max-width: 768px) {
            .sensor-summary-horizontal {
                font-size: 0.9rem;
                padding: 12px 15px;
            }
            .sensor-summary-horizontal pre {
                gap: 10px;
            }
        }
        .sensor-summary-grid {
            background: #f0fdf4;
            border: 2px solid #22c55e;
            border-radius: 8px;
            color: #065f46;
            font-size: 0.98rem;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            width: 100%;
        }
        .sensor-summary-grid .title {
            font-weight: bold;
            font-size: 1.4rem;
            color: #065f46;
            text-align: center;
            margin-bottom: 20px;
        }
        .sensor-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            justify-content: center;
        }
        .sensor-box {
            background: white;
            border: 2px solid #22c55e;
            border-radius: 8px;
            padding: 12px 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 140px;
        }
        .sensor-box.severity-low {
            border-color: #3b82f6;
        }
        .sensor-box.severity-high {
            border-color: #ef4444;
        }
        .sensor-type {
            font-weight: 600;
            color: #065f46;
            margin-bottom: 4px;
            font-size: 0.95rem;
        }
        .sensor-value {
            font-weight: 600;
            color: #047857;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        .gauge-container {
            width: 100%;
            padding: 5px 0;
            position: relative;
        }
        .gauge {
            width: 100%;
            max-width: 80px;
            margin: 0 auto;
            position: relative;
        }
        .gauge-body {
            width: 100%;
            height: 0;
            padding-bottom: 50%;
            background: #e5e7eb;
            position: relative;
            border-top-left-radius: 100% 200%;
            border-top-right-radius: 100% 200%;
            overflow: hidden;
        }
        .gauge-fill {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            height: 100%;
            transform-origin: center top;
            transform: rotate(0.5turn);
            transition: transform 0.2s ease-out, background 0.2s ease-out;
        }
        .gauge-cover {
            width: 75%;
            height: 150%;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 25%;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 0 4px rgba(0,0,0,0.1);
        }
        .severity-indicator {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
            margin-top: 8px;
        }
        @media (max-width: 768px) {
            .sensor-box {
                min-height: 120px;
            }
            .sensor-value {
                font-size: 1rem;
                margin-bottom: 6px;
            }
            .gauge {
                max-width: 65px;
            }
            .gauge-cover {
                width: 70%;
            }
            .severity-indicator {
                font-size: 0.7rem;
            }
        }
        .questions-container {
            position: relative;
            overflow: hidden;
            height: 600px; /* Fixed height */
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .question-slide {
            display: none;
            width: 100%;
            padding: 20px;
            background: white;
            border-radius: 8px;
            transition: opacity 0.3s ease;
            overflow-y: auto; /* Make individual slides scrollable */
            max-height: calc(100% - 100px); /* Leave space for navigation */
            flex: 1;
        }
        .question-slide.active {
            display: block;
            opacity: 1;
        }
        .question-slide.previous {
            display: none;
        }
        .sensor-questions {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px; /* Reduced margin */
        }
        .navigation-buttons {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 8px 8px;
            z-index: 10;
            margin-top: auto; /* Push to bottom */
        }
        .progress-bar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .answers-group {
            margin-top: 20px;
        }
        .answers-group label {
            display: block;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .answers-group label:hover {
            background: #f0fdf4;
            border-color: #22c55e;
        }
        .answers-group input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            cursor: pointer;
        }
        .answers-group span {
            font-size: 1.1rem;
            color: #374151;
            cursor: pointer;
        }
        /* Updated container and panel styles for better iPad support */
        .container.mx-auto {
            width: 100%;
            padding: 2rem;
            box-sizing: border-box;
            display: flex !important;
            flex-direction: row !important;
            gap: 2rem;
            max-width: 100%;
            min-height: calc(100vh - 200px);
            flex-wrap: nowrap !important;
        }
        .readings-panel {
            flex: 2 1 auto !important;
            min-width: 0;
            margin-right: 2rem;
            max-width: calc(100% - 400px);
            background: #ffffff;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .user-panel {
            flex: 0 0 350px !important;
            min-width: 350px !important;
            max-width: 350px !important;
            position: sticky;
            top: 2rem;
            align-self: flex-start;
            height: fit-content;
            background: #ffffff;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        /* Media queries for different screen sizes */
        @media (max-width: 1200px) {
            .container.mx-auto {
                padding: 1.5rem;
                gap: 1.5rem;
            }
            .readings-panel {
                max-width: calc(100% - 350px);
            }
        }
        @media (max-width: 1024px) {
            .container.mx-auto {
                padding: 1rem;
                gap: 1rem;
            }
            .readings-panel {
                margin-right: 1rem;
                max-width: calc(100% - 350px);
            }
        }
        @media (max-width: 768px) {
            .container.mx-auto {
                flex-direction: column !important;
                padding: 0.5rem;
            }
            .readings-panel {
                width: 100%;
                margin-right: 0;
                margin-bottom: 1rem;
                max-width: 100%;
            }
            .user-panel {
                width: 100%;
                min-width: 100% !important;
                max-width: 100% !important;
                position: relative;
                top: 0;
            }
            .sensor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .sensor-grid {
                grid-template-columns: 1fr;
            }
        }
        /* Reset container styles */
        .container.mx-auto {
            width: 100%;
            padding: 2rem;
            box-sizing: border-box;
        }
        
        /* New flex container */
        .flex-container {
            display: flex;
            flex-direction: row;
            gap: 2rem;
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* Panel styles */
        .readings-panel {
            flex: 1;
            min-width: 0;
            background: #ffffff;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .user-panel {
            width: 350px;
            flex-shrink: 0;
            background: #ffffff;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Media queries */
        @media (max-width: 1200px) {
            .flex-container {
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 1024px) {
            .flex-container {
                gap: 1rem;
            }
            .user-panel {
                width: 300px;
            }
        }
        
        @media (max-width: 768px) {
            .flex-container {
                flex-direction: column;
            }
            .readings-panel {
                width: 100%;
            }
            .user-panel {
                width: 100%;
            }
            .sensor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .sensor-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation Styles */
        .ai-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            max-width: 1200px;
            padding: 2rem;
            width: 100%;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #22c55e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }

        .loading-text {
            font-size: 1.25rem;
            color: #374151;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .loading-dots {
            display: flex;
            gap: 4px;
            margin-top: 1rem;
        }

        .loading-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: dotPulse 1.4s ease-in-out infinite;
        }

        .loading-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes dotPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .ai-loading {
                min-height: 250px;
                padding: 1.5rem;
            }

            .loading-spinner {
                width: 50px;
                height: 50px;
            }

            .loading-text {
                font-size: 1.1rem;
            }
        }
    </style>
    <script src="jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        function updateUserDetails() {
            $.get('UIDContainer.php', function(uid) {
                uid = uid.trim();
                if (!uid) {
                    // No UID, show N/A
                    $("#uid").text("N/A");
                    $("#name").text("N/A");
                    $("#email").text("N/A");
                    $("#age").text("N/A");
                    $("#weight").text("N/A");
                    $("#height").text("N/A");
                    $("#gender").text("N/A");
                    $("#myResultsButton").hide();
                    return;
                }
                $("#uid").text(uid);
                // Fetch user details from handle_rfid.php
                $.get('handle_rfid.php', { uid: uid }, function(data) {
                    if (data && !data.error) {
                        $("#name").text(data.name || "N/A");
                        $("#email").text(data.email || "N/A");
                        $("#age").text(data.age ? data.age + " years old" : "N/A");
                        $("#weight").text(data.weight ? data.weight + " kg" : "N/A");
                        $("#height").text(data.height ? data.height + " cm" : "N/A");
                        $("#gender").text(data.gender || "N/A");
                        $("#myResultsButton").show();
                    } else {
                        $("#name").text("N/A");
                        $("#email").text("N/A");
                        $("#age").text("N/A");
                        $("#weight").text("N/A");
                        $("#height").text("N/A");
                        $("#gender").text("N/A");
                        $("#myResultsButton").hide();
                    }
                }, 'json');
            });
        }
        updateUserDetails();
        setInterval(updateUserDetails, 1000); // Update every second to reflect live reading.php
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById("diagnosisButton").addEventListener("click", function (event) {
            const uid = document.getElementById("uid").innerText.trim();
            const temp = document.getElementById("temp").innerText.trim();
            const ecg = document.getElementById("ecg").innerText.trim();
            const pulseRate = document.getElementById("pulse_rate").innerText.trim();
            const spo2 = document.getElementById("spo2").innerText.trim();
            const bp = document.getElementById("bp").innerText.trim();

            // Collect all invalids
            let invalids = [];

            if (!uid || uid === "N/A") {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No valid UID detected',
                    text: 'Please scan your RFID card.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#3085d6',
                    timer: 3500,
                    timerProgressBar: true
                });
                return;
            }
            if (!temp || temp === "0.00 °C" || temp === "N/A °C") {
                invalids.push("Body Temperature");
            }
            if (!ecg || ecg === "0.00" || ecg === "N/A") {
                invalids.push("ECG");
            }
            if (!pulseRate || pulseRate === "0 BPM" || pulseRate === "N/A BPM") {
                invalids.push("Pulse Rate");
            }
            if (!spo2 || spo2 === "0.00 %" || spo2 === "N/A %") {
                invalids.push("SpO₂");
            }
            if (!bp || bp === "N/A mmHg") {
                invalids.push("Blood Pressure");
            }

            if (invalids.length > 0) {
                event.preventDefault();
                let msg = "";
                if (invalids.length === 1) {
                    // Specific instructions for each
                    switch (invalids[0]) {
                        case "Body Temperature":
                            msg = "Please retry. Body temperature not detected.";
                            break;
                        case "ECG":
                            msg = "Please attach the ECG pads.";
                            break;
                        case "Pulse Rate":
                        case "SpO₂":
                            msg = "Please place your finger into the sensor.";
                            break;
                        case "Blood Pressure":
                            msg = "Please press the BP button and attach the cuff properly.";
                            break;
                    }
                } else {
                    msg = "The following readings are missing or zero: " + invalids.join(", ") + ". Please check all sensors and try again.";
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid or Missing Readings',
                    text: msg,
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#3085d6',
                    timer: 4000,
                    timerProgressBar: true
                });
                return;
            }
        });

        document.getElementById("quickSaveButton").addEventListener("click", async function () {
            try {
                const uid = document.getElementById("uid").innerText.trim();
                const name = document.getElementById("name").innerText.trim();
                const email = document.getElementById("email").innerText.trim();
                const gender = document.getElementById("gender").innerText.trim();
                const age = document.getElementById("age").innerText.trim();
                const height = document.getElementById("height").innerText.trim();
                const weight = document.getElementById("weight").innerText.trim();
                const bodyTemp = document.getElementById("temp").innerText.replace(" °C", "").trim();
                const ecg = document.getElementById("ecg").innerText.trim();
                const pulseRate = document.getElementById("pulse_rate").innerText.replace(" BPM", "").trim();
                const spo2 = document.getElementById("spo2").innerText.replace(" %", "").trim();
                const bp = document.getElementById("bp").innerText.replace(" mmHg", "").trim();

                if (!uid || uid === "N/A") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No valid UID detected',
                        text: 'Please scan your RFID card.',
                        confirmButtonText: 'Okay',
                        confirmButtonColor: '#3085d6',
                        timer: 3000, // Auto-dismiss after 3 seconds
                        timerProgressBar: true,
                        didOpen: () => {
                            const progressBar = Swal.getHtmlContainer().querySelector('.swal2-timer-progress-bar');
                            progressBar.style.animation = 'none'; // Reset animation
                            progressBar.style.transformOrigin = 'left'; // Set origin to left
                        }
                    });
                    return;
                }

                const response = await fetch("store_reading.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        uid,
                        name,
                        email,
                        gender,
                        age,
                        height,
                        weight,
                        body_temperature: bodyTemp,
                        ecg,
                        pulse_rate: pulseRate,
                        spo2,
                        blood_pressure: bp,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Reading successfully stored!',
                        confirmButtonText: 'Okay',
                        confirmButtonColor: '#28a745',
                        timer: 3000, // Auto-dismiss after 3 seconds
                        timerProgressBar: true,
                        didOpen: () => {
                            const progressBar = Swal.getHtmlContainer().querySelector('.swal2-timer-progress-bar');
                            progressBar.style.animation = 'none'; // Reset animation
                            progressBar.style.transformOrigin = 'left'; // Set origin to left
                        }
                    }).then(() => {
                        window.location.href = "results2.php"; // Redirect to results2.php
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to store reading. Please try again.',
                        confirmButtonText: 'Okay',
                        confirmButtonColor: '#d33',
                        timer: 3000, // Auto-dismiss after 3 seconds
                        timerProgressBar: true,
                        didOpen: () => {
                            const progressBar = Swal.getHtmlContainer().querySelector('.swal2-timer-progress-bar');
                            progressBar.style.animation = 'none'; // Reset animation
                            progressBar.style.transformOrigin = 'left'; // Set origin to left
                        }
                    });
                }
            } catch (error) {
                console.error("Error storing reading:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while storing the reading.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#d33',
                    timer: 3000, // Auto-dismiss after 3 seconds
                    timerProgressBar: true,
                    didOpen: () => {
                        const progressBar = Swal.getHtmlContainer().querySelector('.swal2-timer-progress-bar');
                        progressBar.style.animation = 'none'; // Reset animation
                        progressBar.style.transformOrigin = 'left'; // Set origin to left
                    }
                });
            }
        });

        // Add event listener for Consult button
        document.getElementById("consultButton").addEventListener("click", function () {
            Swal.fire({
                icon: 'question',
                title: 'Consult Feature',
                text: 'Are you sure you want to continue using the consult feature? This process may take some time, as our AI will learn from your current situation, including any symptoms or conditions you are experiencing.',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "consult.php";
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            var form = document.querySelector('form[action=""]');
            var textarea = document.getElementById("consultInput");
            var sensorSummary = <?php echo json_encode($sensor_summary); ?>;
            // Add hidden input for sensor_summary so it is always POSTed
            if (form && sensorSummary) {
                var hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "sensor_summary";
                hidden.value = sensorSummary;
                form.appendChild(hidden);
            }
            if (form && textarea && sensorSummary) {
                form.addEventListener("submit", function(e) {
                    if (sensorSummary && textarea.value.indexOf(sensorSummary) === -1) {
                        textarea.value = textarea.value.trim() + "\n\n" + sensorSummary;
                    }
                });
            }
        });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('questionnaire-form');
    const questions = document.querySelectorAll('.question-slide');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    const currentQuestionSpan = document.getElementById('current-question');
    const currentQuestionNav = document.getElementById('current-question-nav');
    const progressFill = document.getElementById('progress-fill');
    let currentQuestion = 0;
    const totalQuestions = questions.length;

    function updateNavigation() {
        const progress = ((currentQuestion + 1) / totalQuestions) * 100;
        progressFill.style.width = `${progress}%`;
        currentQuestionSpan.textContent = currentQuestion + 1;
        currentQuestionNav.textContent = currentQuestion + 1;

        questions.forEach((q, index) => {
            q.classList.remove('active', 'previous');
            if (index === currentQuestion) {
                q.classList.add('active');
            } else if (index < currentQuestion) {
                q.classList.add('previous');
            }
        });

        prevBtn.disabled = currentQuestion === 0;
        if (currentQuestion === totalQuestions - 1) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    function areAllQuestionsAnswered(slide) {
        const questionGroups = slide.querySelectorAll('.question-group');
        return Array.from(questionGroups).every(group => {
            const radioInputs = group.querySelectorAll('input[type="radio"]');
            return Array.from(radioInputs).some(input => input.checked);
        });
    }

    prevBtn.addEventListener('click', () => {
        if (currentQuestion > 0) {
            currentQuestion--;
            updateNavigation();
        }
    });

    nextBtn.addEventListener('click', () => {
        const currentSlide = questions[currentQuestion];
        if (!areAllQuestionsAnswered(currentSlide)) {
            const unansweredCount = Array.from(currentSlide.querySelectorAll('.question-group')).filter(group => {
                const radioInputs = group.querySelectorAll('input[type="radio"]');
                return !Array.from(radioInputs).some(input => input.checked);
            }).length;

            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Answers',
                text: `Please answer all ${unansweredCount} remaining question${unansweredCount > 1 ? 's' : ''} before proceeding.`,
                confirmButtonText: 'Okay',
                confirmButtonColor: '#3085d6',
                timer: 3000,
                timerProgressBar: true
            });
            return;
        }

        if (currentQuestion < totalQuestions - 1) {
            currentQuestion++;
            updateNavigation();
        }
    });

    questions.forEach(slide => {
        const radioInputs = slide.querySelectorAll('input[type="radio"]');
        radioInputs.forEach(input => {
            input.addEventListener('change', () => {
                const questionGroup = input.closest('.question-group');
                if (input.checked) {
                    questionGroup.style.border = 'none';
                }
            });
        });
    });

    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const oldReview = document.querySelector('.review-section');
        if (oldReview) oldReview.remove();

        const reviewSection = document.createElement('div');
        reviewSection.className = 'review-section bg-white rounded-lg shadow-lg p-6 mt-6';
        reviewSection.innerHTML = `
            <h3 class="text-2xl font-bold text-green-800 mb-4">Review Your Answers</h3>
            <div class="review-content"></div>
            <div class="flex justify-between mt-6">
                <button type="button" id="edit-answers" class="bg-gray-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-gray-700 transition">Edit Answers</button>
                <div>
                    <button type="button" id="print-review" class="bg-blue-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-blue-700 transition mr-2">Print</button>
                    <button type="button" id="confirm-submit" class="bg-green-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-green-700 transition">Submit</button>
                </div>
            </div>
        `;

        const reviewContent = reviewSection.querySelector('.review-content');
        let hasAnswers = false;

        questions.forEach((slide, slideIndex) => {
            const sensorType = slide.querySelector('.sensor-type') ? slide.querySelector('.sensor-type').textContent : '';
            const sensorValue = slide.querySelector('.sensor-value') ? slide.querySelector('.sensor-value').textContent : '';

            const questionGroups = slide.querySelectorAll('.question-group');
            questionGroups.forEach((group, qIndex) => {
                const question = group.querySelector('p').textContent;
                const selectedAnswer = group.querySelector('input[type="radio"]:checked');
                if (selectedAnswer) {
                    hasAnswers = true;
                    const answerDiv = document.createElement('div');
                    answerDiv.className = 'mb-4 p-4 bg-gray-50 rounded-lg';
                    // Remove () from sensorType/sensorValue display
                    answerDiv.innerHTML = `
                        <div class="font-semibold text-green-800">${sensorType} - ${sensorValue}</div>
                        <div class="text-gray-700 mt-2">${question}</div>
                        <div class="text-gray-600 mt-1">Your answer: ${selectedAnswer.value}</div>
                    `;
                    reviewContent.appendChild(answerDiv);
                }
            });
        });

        if (!hasAnswers) {
            Swal.fire({
                icon: 'warning',
                title: 'No Answers Selected',
                text: 'Please answer at least one question before submitting.',
                confirmButtonText: 'Okay',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        document.querySelector('.questions-container').style.display = 'none';
        document.querySelector('.consult-input-col').appendChild(reviewSection);

        document.getElementById('edit-answers').addEventListener('click', function() {
            reviewSection.remove();
            document.querySelector('.questions-container').style.display = 'block';
        });

        // Print function for review
        document.getElementById('print-review').addEventListener('click', function() {
            const printContents = reviewSection.querySelector('.review-content').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Review Answers</title>');
            printWindow.document.write('<style>body{font-family:sans-serif;padding:24px;} .font-semibold{font-weight:bold;} .text-green-800{color:#065f46;} .text-gray-700{color:#374151;} .text-gray-600{color:#4B5563;} .mb-4{margin-bottom:1rem;} .p-4{padding:1rem;} .bg-gray-50{background:#f9fafb;} .rounded-lg{border-radius:0.5rem;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2>Review Your Answers</h2>');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });

        document.getElementById('confirm-submit').addEventListener('click', async function(e) {
            e.preventDefault();
            
            try {
                // Gather sensor readings from the summary grid
                let sensorReadings = [];
                document.querySelectorAll('.sensor-box').forEach(box => {
                    const type = box.querySelector('.sensor-type')?.textContent?.trim();
                    const value = box.querySelector('.sensor-value')?.textContent?.trim();
                    if (type && value) {
                        sensorReadings.push(`- ${type}: ${value}`);
                    }
                });

                // Gather user answers from the review section
                let answers = [];
                reviewSection.querySelectorAll('.review-content > div').forEach(div => {
                    const sensor = div.querySelector('.font-semibold')?.innerText?.trim();
                    const question = div.querySelector('.text-gray-700')?.innerText?.trim();
                    const answer = div.querySelector('.text-gray-600')?.innerText?.replace('Your answer: ', '').trim();
                    if (sensor && question && answer) {
                        answers.push(`- ${sensor} Q: ${question}\n  A: ${answer}`);
                    }
                });

                // Get user context
                const uid = document.getElementById('uid')?.innerText?.trim() || '';
                const email = document.getElementById('email')?.innerText?.trim() || '';
                const name = document.getElementById('name')?.innerText?.trim() || '';
                const age = document.getElementById('age')?.innerText?.trim() || '';
                const gender = document.getElementById('gender')?.innerText?.trim() || '';

                if (!uid || uid === 'N/A') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No valid UID found. Please scan your RFID card again.',
                        confirmButtonText: 'Okay'
                    });
                    return;
                }

                // Build prompt
                let prompt = "Sensor Readings:\n";
                prompt += sensorReadings.join('\n') + "\n\n";
                prompt += "Answers:\n";
                prompt += answers.join('\n') + "\n\n";
                prompt += `Patient context: Age: ${age}, Gender: ${gender}\n\n`;
                prompt += "Please provide a medical diagnosis and explain the possible condition and next steps.";

                // Show loading state with animation
                const container = document.querySelector('.container.mx-auto');
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'ai-loading';
                loadingDiv.innerHTML = `
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Consulting AI, please wait...</div>
                    <div class="loading-dots">
                        <div class="loading-dot"></div>
                        <div class="loading-dot"></div>
                        <div class="loading-dot"></div>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(loadingDiv);

                // Send request to consult.php
                const formData = new FormData();
                formData.append('consultInput', prompt);
                formData.append('uid', uid);
                formData.append('email', email);
                formData.append('name', name);
                formData.append('age', age);
                formData.append('gender', gender);

                const response = await fetch('consult.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.text();
                
                // Create a temporary div to parse the HTML response
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = result;

                // Extract the AI response content
                const aiResponse = tempDiv.querySelector('.prose')?.innerHTML || result;
                
                // Create and display the AI diagnosis container
                const aiContainer = document.createElement('div');
                aiContainer.className = 'ai-diagnosis-container';
                aiContainer.innerHTML = `
                    <div class="ai-diagnosis-header text-center mb-6">
                        <h2 class="text-3xl font-bold text-green-800">AI Diagnosis Result</h2>
                        <p class="text-gray-600">Based on your vital signs and responses</p>
                    </div>
                    <div class="ai-diagnosis-content">
                        <div class="diagnosis-section">
                            ${aiResponse}
                        </div>
                    </div>
                `;

                // Scroll to top immediately before showing results
                window.scrollTo(0, 0);

                // Replace loading state with results
                container.innerHTML = '';
                container.appendChild(aiContainer);

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request. Please try again.',
                    confirmButtonText: 'Okay'
                });
            }
        });
    });

    updateNavigation();
});
</script>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <div class="bg-overlay min-h-screen">
        
    <?php if (empty($ai_responses)): ?>
    <header id="header" class="header d-flex align-items-center position-relative">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.php" class="logo d-flex align-items-center">
                <img src="img/logo.png" alt="AgriCulture">
            </a>

            <nav id="navmenu">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="registration2.php">Registration</a></li>
                    <li><a href="userdata2.php">User Data</a></li>
                    <li><a href="live reading.php" class="active">Live-Reading</a></li>
                    <li><a href="results2.php">Results</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </nav>

        </div>
    </header>
    <?php endif; ?>

    <div class="container mx-auto px-4 py-8">
        <?php if (empty($ai_responses)): ?>
        <div class="flex-container">
            <!-- Input box and submit button panel -->
            <div class="readings-panel">
                <form action="" method="post" class="w-full">
                    <?php if (!empty($sensor_summary)): ?>
                    <div id="sensor-summary-container" class="sensor-summary-grid mb-6">
                        <div class="title mb-4">Sensor Summary</div>
                        <div class="sensor-grid">
                            <?php
                            $sensor_data = explode("\n", trim($sensor_summary));
                            for ($i = 0; $i < count($sensor_data); $i += 2) {
                                if (isset($sensor_data[$i]) && isset($sensor_data[$i + 1])) {
                                    $type = trim($sensor_data[$i]);
                                    $value = trim($sensor_data[$i + 1]);
                                    
                                    // Extract numeric value for gauge
                                    $numeric_value = 0;
                                    $severity = 'normal';
                                    $max_value = 0;
                                    
                                    switch($type) {
                                        case 'Temp':
                                            $numeric_value = floatval(str_replace('°C', '', $value));
                                            $max_value = 42; // Max temperature for gauge
                                            if ($numeric_value < 36.1) $severity = 'low';
                                            else if ($numeric_value > 37.2) $severity = 'high';
                                            break;
                                        case 'ECG':
                                            $numeric_value = floatval($value);
                                            $max_value = 150; // Max ECG for gauge
                                            if ($numeric_value < 60) $severity = 'low';
                                            else if ($numeric_value > 100) $severity = 'high';
                                            break;
                                        case 'Pulse':
                                            $numeric_value = floatval(str_replace('BPM', '', $value));
                                            $max_value = 150; // Max pulse for gauge
                                            if ($numeric_value < 60) $severity = 'low';
                                            else if ($numeric_value > 100) $severity = 'high';
                                            break;
                                        case 'SpO₂':
                                            $numeric_value = floatval(str_replace('%', '', $value));
                                            $max_value = 100; // Max SpO2 for gauge
                                            if ($numeric_value < 95) $severity = 'low';
                                            else if ($numeric_value > 100) $severity = 'high';
                                            break;
                                        case 'BP':
                                            $bp_parts = explode('/', str_replace('mmHg', '', $value));
                                            if (count($bp_parts) === 2) {
                                                $systolic = floatval($bp_parts[0]);
                                                $diastolic = floatval($bp_parts[1]);
                                                $numeric_value = $systolic; // Use systolic for gauge
                                                $max_value = 180; // Max BP for gauge
                                                if ($systolic < 90 || $diastolic < 60) $severity = 'low';
                                                else if ($systolic > 120 || $diastolic > 80) $severity = 'high';
                                            }
                                            break;
                                    }
                                    
                                    // Calculate gauge rotation based on value and max
                                    $gauge_rotation = min(($numeric_value / $max_value) * 180, 180);
                                    
                                    // Set gauge color based on severity
                                    $gauge_color = '#22c55e'; // normal - green
                                    if ($severity === 'low') {
                                        $gauge_color = '#3b82f6'; // low - blue
                                    } else if ($severity === 'high') {
                                        $gauge_color = '#ef4444'; // high - red
                                    }
                                    
                                    echo '<div class="sensor-box severity-' . $severity . '">
                                            <div class="sensor-type">' . htmlspecialchars($type) . '</div>
                                            <div class="sensor-value">' . htmlspecialchars($value) . '</div>
                                            <div class="gauge-container">
                                                <div class="gauge">
                                                    <div class="gauge-body">
                                                        <div class="gauge-fill" style="transform: rotate(' . $gauge_rotation . 'deg); background: ' . $gauge_color . ';"></div>
                                                        <div class="gauge-cover"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="severity-indicator ' . $severity . '">
                                                ' . ucfirst($severity) . '
                                            </div>
                                        </div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="consult-input-col">
                        <form action="" method="post" class="w-full">
                            <?php if ($has_abnormal_readings): ?>
                                <div class="questions-container">
                                    <div class="progress-bar">
                                        <div class="progress-text text-sm text-gray-600 mb-2">
                                            Question <span id="current-question">1</span> of <?php echo count($abnormal_sensors); ?>
                                        </div>
                                        <div class="progress-track bg-gray-200 rounded-full h-2">
                                            <div id="progress-fill" class="bg-green-500 h-2 rounded-full" style="width: 0%"></div>
                                        </div>
                                    </div>

                                    <form action="" method="post" class="w-full" id="questionnaire-form">
                                        <?php foreach ($abnormal_sensors as $index => $sensor): ?>
                                            <div class="question-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-question="<?php echo $index + 1; ?>">
                                                <div class="sensor-questions">
                                                    <h4 class="text-xl font-semibold text-green-800 mb-4">
                                                        <?php echo ($index + 1) . '. ' . htmlspecialchars($sensor['type']) . ' - ' . htmlspecialchars($sensor['value']); ?>
                                                    </h4>
                                                    
                                                    <?php
                                                    // Get questions for this sensor type and severity
                                                    $questions = $sensor_questions[$sensor['type']][$sensor['severity']] ?? [];
                                                    foreach ($questions as $qIndex => $question): ?>
                                                        <div class="question-group mb-6">
                                                            <p class="text-lg text-gray-700 mb-4"><?php echo ($qIndex + 1) . '. ' . htmlspecialchars($question); ?></p>
                                                            <div class="answers-group">
                                                                <label class="flex items-center">
                                                                    <input type="radio" name="<?php echo $sensor['type'] . '_' . $qIndex; ?>" value="yes" class="form-radio text-green-600">
                                                                    <span>Yes</span>
                                                                </label>
                                                                <label class="flex items-center">
                                                                    <input type="radio" name="<?php echo $sensor['type'] . '_' . $qIndex; ?>" value="no" class="form-radio text-green-600">
                                                                    <span>No</span>
                                                                </label>
                                                                <label class="flex items-center">
                                                                    <input type="radio" name="<?php echo $sensor['type'] . '_' . $qIndex; ?>" value="sometimes" class="form-radio text-green-600">
                                                                    <span>Sometimes</span>
                                                                </label>
                                                                <label class="flex items-center">
                                                                    <input type="radio" name="<?php echo $sensor['type'] . '_' . $qIndex; ?>" value="not_sure" class="form-radio text-green-600">
                                                                    <span>Not sure</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="navigation-buttons">
                                            <div class="flex justify-between items-center">
                                                <button type="button" id="prev-btn" class="bg-gray-400 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-gray-600 transition" disabled>Previous</button>
                                                <div class="text-gray-600">
                                                    Question <span id="current-question-nav">1</span> of <?php echo count($abnormal_sensors); ?>
                                                </div>
                                                <button type="button" id="next-btn" class="bg-green-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-green-700 transition">Next</button>
                                                <button type="submit" id="submit-btn" class="bg-green-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-green-700 transition hidden">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 bg-white rounded-lg shadow-lg">
                                    <h3 class="text-2xl font-bold text-green-900 mb-4">All readings are within normal range</h3>
                                    <p class="text-lg text-gray-700 mb-6">No additional questions are needed at this time.</p>
                                    <a href="live reading.php" class="bg-green-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-green-700 transition inline-block">Back to Readings</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </form>
            </div>
            <!-- User Details Panel -->
            <div class="user-panel">
                <h3>User Details</h3>
                <table>
                    <tr>
                        <td>UID:</td>
                        <td id="uid">N/A</td>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td id="name">N/A</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td id="email">N/A</td>
                    </tr>
                    <tr>
                        <td>Age:</td>
                        <td id="age">N/A</td>
                    </tr>
                    <tr>
                        <td>Weight:</td>
                        <td id="weight">N/A</td>
                    </tr>
                    <tr>
                        <td>Height:</td>
                        <td id="height">N/A</td>
                    </tr>
                    <tr>
                        <td>Gender:</td>
                        <td id="gender">N/A</td>
                    </tr>
                </table>
                <div class="mt-4" id="myResultsButton" style="display: none;">
                    <a href="my_results.php" class="bg-green-500 text-white px-16 py-12 rounded-lg text-4xl font-bold hover:bg-green-800 block w-full h-40 flex items-center justify-center">My Results</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- AI Diagnosis Results -->
        <div class="ai-diagnosis-container">
            <div class="ai-diagnosis-content">
                <?php foreach ($ai_responses as $idx => $resp): ?>
                    <div class="diagnosis-section">
                        <?= nl2br(htmlspecialchars($resp)) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>