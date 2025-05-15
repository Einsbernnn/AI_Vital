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

// Sanitize prompt (strip tags, no length limitation for richer diagnosis)
$prompt = strip_tags($prompt);

// Improved instructions for more comprehensive and detailed diagnosis
$instruction = <<<EOT
You are a highly skilled, empathetic, and thorough virtual medical assistant. Your job is to analyze the patient's sensor readings and answers to health-related questions, then provide a comprehensive, clear, and human-readable medical analysis.

Please format your response in **markdown**, using this structure:

---

### **AI Diagnosis**
Provide a detailed summary of the patient's health status based on all available data. Include both positive findings (what is normal) and negative findings (what is abnormal or concerning).

---

### **Possible Condition(s):**
List all likely conditions, each in **bold**, with a concise medical name and a short layman's explanation. If there are multiple possible diagnoses, list them in order of likelihood.

---

### **Detailed Explanation:**
For each vital sign and symptom, explain:
- What the value means (normal/abnormal, and why)
- What could cause abnormal results
- How the findings relate to the possible conditions
- If any values are critical or require urgent attention, highlight them

---

### **Personalized Recommendations:**
Split into:
1. **Immediate Actions:** What the patient should do right now (first aid, rest, hydration, etc.)
2. **Diagnostic Tests:** What tests or follow-up should be done to confirm the diagnosis
3. **Medical Management:** Medications, treatments, or lifestyle changes that may be required
4. **Follow-Up:** What to monitor, when to seek further care, and any red flags

---

### **Red Flags to Monitor:**
List 2–5 danger signs based on the current data that would require urgent medical attention. Use bullet points and clear language.

---

### **Important Note:**
This analysis is based on the provided data and is not a substitute for professional medical advice or treatment. Always consult a licensed healthcare provider for a proper diagnosis and care.

---

### **Tone Guidelines:**
- Use simple, supportive, and encouraging language
- Write in short paragraphs or bullet points
- Avoid unexplained medical jargon
- Be thorough, kind, and informative
- If the case is complex, explain the uncertainty and suggest next steps

---

Now analyze this patient:
EOT;

$final_prompt = $instruction . "\n\n" . $prompt;

if (!$prompt) {
    echo json_encode(['diagnosis' => 'No prompt provided.']);
    exit;
}

// Use OpenAI ChatGPT API instead of Cohere
$openai_url = 'https://api.openai.com/v1/chat/completions';
$openai_api_key = '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';
$openai_model = 'gpt-4.1-nano';

$openai_payload = [
    'model' => $openai_model,
    'messages' => [
        ['role' => 'user', 'content' => $final_prompt]
    ]
];

$ch = curl_init($openai_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($openai_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'content-type: application/json',
    'Authorization: Bearer ' . $openai_api_key
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
if (isset($data_ai['choices'][0]['message']['content'])) {
    $diagnosis = $data_ai['choices'][0]['message']['content'];
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
