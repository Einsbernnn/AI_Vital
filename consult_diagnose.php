<?php
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$prompt = $data['prompt'] ?? '';

// Sanitize and limit prompt length (max 2000 chars, strip tags)
$prompt = strip_tags($prompt);
$prompt = mb_substr($prompt, 0, 2000);

// New: Use detailed markdown instructions for the AI
$instruction = <<<EOT
You are a professional virtual medical assistant. Based on the patient’s sensor readings and answers to health-related questions, provide a clear, human-readable medical analysis.

Please format your response in **markdown**, using this structure:

---

### **AI Diagnosis**
Based on the sensor readings, patient responses, and context, here’s a **possible medical diagnosis** and **next steps**:

---

### **Possible Condition:**
Give the most likely condition in **bold**, followed by a concise medical name (e.g., **Heat Exhaustion with Respiratory Distress and Hypertension**)

---

### **Explanation:**
Explain the diagnosis clearly, breaking it into sections if needed:
1. **Body Temperature:** Explain if the temp is normal, high, or dangerous.
2. **ECG:** Describe any arrhythmia, abnormalities, or if it’s normal.
3. **Pulse Rate:** State if it’s bradycardic, tachycardic, or normal.
4. **SpO₂:** Explain if oxygen saturation is safe or needs intervention.
5. **Blood Pressure:** Indicate if it's normal, elevated, or hypertensive.

Include related symptoms from the Q&A (e.g., fever, chills, dizziness, etc.).

---

### **Next Steps:**
Split into 4 subcategories:
1. **Immediate Actions:** Basic first aid steps, what to do right now.
2. **Diagnostic Tests:** Suggested medical tests to confirm the issue.
3. **Medical Management:** Medications or treatments that may be required.
4. **Follow-Up:** Recommendations for what to monitor and when to consult a doctor.

---

### **Red Flags to Monitor:**
List 2–3 danger signs based on the current data that would require urgent care.

---

### **Important Note:**
This analysis is not a substitute for professional medical advice or treatment. Always consult a licensed healthcare provider for a proper diagnosis and care.

---

### **Tone Guidelines:**
- Use **simple language** a patient can understand.
- Write in short paragraphs or bullet points.
- Avoid long medical jargon unless explained.
- Be helpful, kind, and informative.

---

Now analyze this patient:
EOT;

$final_prompt = $instruction . "\n\n" . $prompt;

if (!$prompt) {
    echo json_encode(['diagnosis' => 'No prompt provided.']);
    exit;
}

$cohere_url = 'https://api.cohere.com/v2/chat';
$api_key = 'F3LM9ycUnenzInMB2m94RWdRwHuQLnTH7cT2f5qB';
$model = 'command-a-03-2025';

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'user', 'content' => $final_prompt]
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
    echo json_encode(['diagnosis' => 'Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
$data_ai = json_decode($result, true);
curl_close($ch);

$diagnosis = '';
if (isset($data_ai['text'])) {
    $diagnosis = $data_ai['text'];
} elseif (isset($data_ai['reply'])) {
    $diagnosis = $data_ai['reply'];
} elseif (isset($data_ai['message']['content'][0]['text'])) {
    $diagnosis = $data_ai['message']['content'][0]['text'];
} elseif (isset($data_ai['content'][0]['text'])) {
    $diagnosis = $data_ai['content'][0]['text'];
} elseif (isset($data_ai['message'])) {
    $diagnosis = is_array($data_ai['message']) ? json_encode($data_ai['message']) : $data_ai['message'];
} elseif (isset($data_ai['error'])) {
    $diagnosis = is_array($data_ai['error']) ? json_encode($data_ai['error']) : $data_ai['error'];
} else {
    $diagnosis = "No response from AI. Raw: " . $result;
}

// --- Store to health_consult table and send email using PHPMailer ---

// Get and sanitize additional fields
$uid = isset($data['uid']) ? substr(preg_replace('/[^A-Za-z0-9\-]/', '', $data['uid']), 0, 255) : '';
$patient_name = isset($data['patient_name']) ? substr(strip_tags($data['patient_name']), 0, 255) : '';
$temperature = isset($data['temperature']) ? floatval($data['temperature']) : null;
$ecg_rate = isset($data['ecg_rate']) ? floatval($data['ecg_rate']) : null;
$pulse_rate = isset($data['pulse_rate']) ? floatval($data['pulse_rate']) : null;
$spo2_level = isset($data['spo2_level']) ? floatval($data['spo2_level']) : null;
$blood_pressure = isset($data['blood_pressure']) ? substr(strip_tags($data['blood_pressure']), 0, 10) : '';
$email = ''; // will be fetched from DB

// Fetch email from DB based on UID (auto, not from client)
$email_found = false;
if ($uid) {
    // Try both health_consult and health_diagnostics databases for robustness
    $mysqli = new mysqli('localhost', 'root', '', 'health_consult');
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare("SELECT email FROM health_diagnostics WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $uid);
            $stmt->execute();
            $stmt->bind_result($db_email);
            if ($stmt->fetch()) {
                $email = $db_email;
                $email_found = true;
            }
            $stmt->close();
        }
        $mysqli->close();
    }
    if (!$email_found) {
        // Try main database if not found in health_consult
        $mysqli = new mysqli('localhost', 'root', '', 'main'); // Change 'main' to your main DB name if needed
        if (!$mysqli->connect_errno) {
            $stmt = $mysqli->prepare("SELECT email FROM health_diagnostics WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $uid);
                $stmt->execute();
                $stmt->bind_result($db_email);
                if ($stmt->fetch()) {
                    $email = $db_email;
                    $email_found = true;
                }
                $stmt->close();
            }
            $mysqli->close();
        }
    }
}

// If no email found, return error
if (!$email_found) {
    echo json_encode([
        'diagnosis' => $diagnosis,
        'email_sent' => false,
        'error' => 'User email not found for UID: ' . $uid
    ]);
    exit;
}

// Send email using PHPMailer (auto, using fetched $email)
$email_status = null;
$email_error = null;
if ($email && $diagnosis) {
    $mail = new PHPMailer(true);
    try {
        // Enable SMTP debug output and log to file
        $mail->SMTPDebug = 2; // 2 = client and server messages
        $mail->Debugoutput = function($str, $level) {
            file_put_contents(__DIR__ . '/phpmailer_debug.log', $str . "\n", FILE_APPEND);
        };
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'einsbernsystem@gmail.com';
        $mail->Password = 'bdov zsdz sidj bcsc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('einsbernsystem@gmail.com', 'AI-Vital Diagnoser');
        $mail->addAddress($email, $patient_name);

        $mail->isHTML(true);
        $mail->Subject = "Your AI-VITAL Medical Diagnosis";
        $mail->Body = "
            <h3>Hello $patient_name,</h3>
            <p>Here are your health details and readings:</p>
            <ul>
                <li><strong>UID:</strong> $uid</li>
                <li><strong>Name:</strong> $patient_name</li>
                <li><strong>Email:</strong> $email</li>
                <li><strong>Age:</strong> " . (isset($data['age']) ? htmlspecialchars($data['age']) : 'N/A') . "</li>
                <li><strong>Weight:</strong> " . (isset($data['weight']) ? htmlspecialchars($data['weight']) : 'N/A') . " kg</li>
                <li><strong>Height:</strong> " . (isset($data['height']) ? htmlspecialchars($data['height']) : 'N/A') . " cm</li>
                <li><strong>Gender:</strong> " . (isset($data['gender']) ? htmlspecialchars($data['gender']) : 'N/A') . "</li>
            </ul>
            <p><strong>Vital Signs:</strong></p>
            <ul>
                <li><strong>Body Temperature:</strong> $temperature °C</li>
                <li><strong>ECG Rate:</strong> $ecg_rate BPM</li>
                <li><strong>Pulse Rate:</strong> $pulse_rate BPM</li>
                <li><strong>SpO₂ Level:</strong> $spo2_level %</li>
                <li><strong>Blood Pressure:</strong> $blood_pressure mmHg</li>
            </ul>
            <p><strong>Diagnosis:</strong></p>
            <div style='white-space: pre-line;'>$diagnosis</div>
            <p>Stay healthy and take care!</p>
        ";
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->send();
            $email_status = true;
        } else {
            $email_status = false;
            $email_error = 'Invalid email address: ' . $email;
        }
    } catch (Exception $e) {
        $email_status = false;
        $email_error = 'Failed to send the email. ' . $mail->ErrorInfo . ' Exception: ' . $e->getMessage();
        // Also log error info
        file_put_contents(__DIR__ . '/phpmailer_debug.log', 'PHPMailer Exception: ' . $mail->ErrorInfo . ' ' . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// Save to health_consult table regardless of email status
if ($uid && $patient_name && $temperature !== null && $ecg_rate !== null && $pulse_rate !== null && $spo2_level !== null && $blood_pressure && $diagnosis) {
    $mysqli = new mysqli('localhost', 'root', '', 'health_consult');
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare("INSERT INTO health_consult (`id`, `patient_name`, `temperature`, `ecg_rate`, `pulse_rate`, `spo2_level`, `blood_pressure`, `consultation`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param(
                "ssdddsss",
                $uid,
                $patient_name,
                $temperature,
                $ecg_rate,
                $pulse_rate,
                $spo2_level,
                $blood_pressure,
                $diagnosis
            );
            $stmt->execute();
            $stmt->close();
        }
        $mysqli->close();
    }
}

// Log the diagnosis with timestamp (simple file log, or replace with DB insert as needed)
$log_entry = date('Y-m-d H:i:s') . "\nPrompt:\n" . $prompt . "\nDiagnosis:\n" . $diagnosis . "\n---\n";
file_put_contents(__DIR__ . '/diagnosis_log.txt', $log_entry, FILE_APPEND);

echo json_encode([
    'diagnosis' => $diagnosis,
    'email_sent' => $email_status,
    'email_error' => $email_error,
]);
