<?php
$diagnosis = "";

// Function to send request to LLaMA 3 locally
function getAiDiagnosis($body_temp, $ecg, $pulse_rate, $spo2) {
    $endpoint = "http://localhost:11434/api/generate"; // Ollama API

    // Improved NLP prompt for AI diagnosis
    $prompt = "You are a highly skilled virtual nurse with expertise in real-time patient diagnostics. 
    Your goal is to analyze a patient's vital signs, determine their health status, and provide recommendations.

    **Patient's Vital Signs:**
    - Body Temperature: $body_temp °C
    - ECG Rate: $ecg BPM
    - Pulse Rate: $pulse_rate BPM
    - SpO2 Level: $spo2 %

    **Your Task as the AI Nurse:**
    1. Determine whether the vitals are **normal, borderline, or critical**.
    2. Identify potential medical conditions (e.g., fever, bradycardia, tachycardia, hypoxia, arrhythmia).
    3. Explain the **causes** behind abnormal readings.
    4. Suggest appropriate medical actions (e.g., drink fluids, rest, seek emergency care).
    5. Offer lifestyle advice and **empathize** with the patient.
    6. If vitals indicate an emergency, give **urgent medical advice**.
    7. Make sure that chances of normal diagnosis are high compare to critical
    8. Always tell last that this are based on the data provided and it is not 100% accurate seek medical advice if needed.
    9. Allways tell that nurse at school is here to assist you
    10. Call them user instead of patient
    
    Make sure your response is clear, medically accurate, and conversational, like a caring nurse.";

    $data = [
        "model" => "llama3",
        "prompt" => $prompt,
        "stream" => false
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);
    return $response_data['response'] ?? "Sorry, I couldn't process your request.";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $body_temp = filter_input(INPUT_POST, "body_temperature", FILTER_VALIDATE_FLOAT);
    $ecg = filter_input(INPUT_POST, "ecg", FILTER_VALIDATE_INT);
    $pulse_rate = filter_input(INPUT_POST, "pulse_rate", FILTER_VALIDATE_INT);
    $spo2 = filter_input(INPUT_POST, "spo2", FILTER_VALIDATE_INT);

    if ($body_temp && $ecg && $pulse_rate && $spo2) {
        // Simulate processing delay
        sleep(3); 
        $diagnosis = getAiDiagnosis($body_temp, $ecg, $pulse_rate, $spo2);
    } else {
        $diagnosis = "Invalid input. Please enter valid numbers for all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Nurse Diagnosis</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #e3f2fd;
            text-align: center;
        }
        h2 {
            color: #1565c0;
            font-weight: bold;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background: #218838;
        }
        .diagnosis {
            margin-top: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .progress-container {
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
            margin-top: 15px;
            display: none;
        }
        .progress-bar {
            width: 0;
            height: 10px;
            background-color: #4caf50;
            border-radius: 5px;
        }
        .loading-text {
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <h2>🩺 AI Nurse Diagnosis</h2>
    <form id="diagnosisForm" action="" method="post">
        <label>Body Temperature (°C):</label>
        <input type="number" step="0.1" name="body_temperature" required>

        <label>ECG Rate (BPM):</label>
        <input type="number" name="ecg" required>

        <label>Pulse Rate (BPM):</label>
        <input type="number" name="pulse_rate" required>

        <label>SpO2 (%):</label>
        <input type="number" name="spo2" required>

        <button type="submit" id="submitBtn">Get AI Diagnosis</button>

        <div class="progress-container" id="progressContainer">
            <div class="progress-bar" id="progressBar"></div>
        </div>
        <p class="loading-text" id="loadingText">Analyzing data... Please wait 🏥</p>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST") : ?>
        <div class="diagnosis">
            <h3>🩺 AI Nurse Diagnosis:</h3>
            <p><?php echo nl2br(htmlspecialchars($diagnosis)); ?></p>
        </div>
    <?php endif; ?>

    <script>
        document.getElementById("diagnosisForm").addEventListener("submit", function() {
            document.getElementById("submitBtn").disabled = true;
            document.getElementById("progressContainer").style.display = "block";
            document.getElementById("loadingText").style.display = "block";

            let progressBar = document.getElementById("progressBar");
            let width = 0;
            let interval = setInterval(function() {
                if (width >= 100) {
                    clearInterval(interval);
                } else {
                    width += 5;
                    progressBar.style.width = width + "%";
                }
            }, 200); // Progress updates every 200ms

        });
    </script>
</body>
</html>