<?php
require 'database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$diagnosis = "";
$message = "";
$name = "";
$email = "";

// Get values from query parameters
$body_temp = isset($_GET['body_temperature']) ? htmlspecialchars($_GET['body_temperature']) : 'N/A';
$ecg = isset($_GET['ecg']) ? htmlspecialchars($_GET['ecg']) : 'N/A';
$pulse_rate = isset($_GET['pulse_rate']) ? htmlspecialchars($_GET['pulse_rate']) : 'N/A';
$spo2 = isset($_GET['spo2']) ? htmlspecialchars($_GET['spo2']) : 'N/A';
$blood_pressure = isset($_GET['blood_pressure']) ? htmlspecialchars($_GET['blood_pressure']) : 'N/A';
$uid = isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : 'N/A';
$age = isset($_GET['age']) ? htmlspecialchars($_GET['age']) : 'N/A';
$weight = isset($_GET['weight']) ? htmlspecialchars($_GET['weight']) : 'N/A';
$height = isset($_GET['height']) ? htmlspecialchars($_GET['height']) : 'N/A';
$gender = isset($_GET['gender']) ? htmlspecialchars($_GET['gender']) : 'N/A';

// Fetch user details from the database
try {
    $conn = Database::connect();
    $query = "SELECT name, email FROM health_diagnostics WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->errorInfo()[2]);
    }

    $stmt->execute([$uid]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found.");
    }

    $name = $user['name'];
    $email = $user['email'];
} catch (Exception $e) {
    $message = "Error fetching user details: " . htmlspecialchars($e->getMessage());
} finally {
    Database::disconnect();
}

// Function to send request to DeepSeek AI locally
function getAiDiagnosis($body_temp, $ecg, $pulse_rate, $spo2, $blood_pressure, $height, $weight) {
    $endpoint = "https://api.cohere.com/v2/chat";
    $api_key = "F3LM9ycUnenzInMB2m94RWdRwHuQLnTH7cT2f5qB";
    $model = "command-a-03-2025";

    $prompt = "You are a highly skilled virtual nurse with expertise in real-time patient diagnostics. 
    Your goal is to analyze a user's vital signs, determine their health status, and provide recommendations.

    **User's Vital Signs:**
    - Body Temperature: $body_temp °C
    - ECG Rate: $ecg BPM
    - Pulse Rate: $pulse_rate BPM
    - SpO2 Level: $spo2 %
    - Blood Pressure: $blood_pressure mmHg
    - Height: $height cm
    - Weight: $weight kg

    **Your Task as the AI Nurse:**
    1. Determine whether the vitals are **normal, borderline, or critical**.
    2. Identify potential medical conditions (e.g., fever, bradycardia, tachycardia, hypoxia, arrhythmia).
    3. Explain the **causes** behind abnormal readings.
    4. Possible sickeness based on user vitals sign result make this explain broadly. Also bold and capital this since this is very crusial.
    5. Suggest appropriate medical actions (e.g., drink fluids, rest, seek emergency care).
    6. Give a possible indication on what are their disease.
    7. If vitals indicate an emergency, give **urgent medical advice**.
    8. Calculate their BMI based on their weight and height.
    9. Always state that the diagnosis is based on the data provided and is not 100% accurate. Advise seeking medical attention if needed.
    10. Always remind the user that the school nurse is available to assist them.";

    $payload = [
        "model" => $model,
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "content-type: application/json",
        "Authorization: bearer $api_key"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    // Handle Cohere's nested response
    if (isset($response_data['text'])) {
        return $response_data['text'];
    } elseif (isset($response_data['reply'])) {
        return $response_data['reply'];
    } elseif (isset($response_data['message']['content'][0]['text'])) {
        return $response_data['message']['content'][0]['text'];
    } elseif (isset($response_data['content'][0]['text'])) {
        return $response_data['content'][0]['text'];
    } elseif (isset($response_data['message'])) {
        $msg = is_array($response_data['message']) ? json_encode($response_data['message']) : $response_data['message'];
        return "API Error: " . $msg;
    } elseif (isset($response_data['error'])) {
        $err = is_array($response_data['error']) ? json_encode($response_data['error']) : $response_data['error'];
        return "API Error: " . $err;
    } else {
        return "No response from AI. Raw: " . $response;
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $diagnosis = getAiDiagnosis($body_temp, $ecg, $pulse_rate, $spo2, $blood_pressure, $height, $weight);

    // Email-sending logic
    try {
        $conn = Database::connect();

        // Prepare the email content
        $subject = "Here Are Your Health Diagnostic Result from Using AI-VITAl";
        $messageBody = "
            <h3>Hello $name,</h3>
            <p>Here are your health readings:</p>
            <ul>
                <li><strong>Body Temperature:</strong> $body_temp °C</li>
                <li><strong>ECG Rate:</strong> $ecg BPM</li>
                <li><strong>Pulse Rate:</strong> $pulse_rate BPM</li>
                <li><strong>SpO₂ Level:</strong> $spo2 %</li>
                <li><strong>Blood Pressure:</strong> $blood_pressure mmHg</li>
            </ul>
            <p><strong>Diagnosis:</strong></p>
            <p style='white-space: pre-line;'>$diagnosis</p>
            <p>Stay healthy and take care!</p>
        ";

        // Send the email using PHPMailer
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'einsbernsystem@gmail.com';
        $mail->Password = 'bdov zsdz sidj bcsc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('einsbernsystem@gmail.com', 'AI-Vital Diagnoser');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $messageBody;

        $mail->send();

        // Insert the readings and diagnosis into the health_readings table
        $insertQuery = "
            INSERT INTO health_readings 
            (id, patient_name, temperature, ecg_rate, pulse_rate, spo2_level, blood_pressure, diagnosis) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $insertStmt = $conn->prepare($insertQuery);

        if (!$insertStmt) {
            throw new Exception("Failed to prepare insert query: " . $conn->errorInfo()[2]);
        }

        $insertStmt->execute([
            $uid,
            $name,
            $body_temp,
            $ecg,
            $pulse_rate,
            $spo2,
            $blood_pressure,
            $diagnosis
        ]);

        $message = "The diagnostic result has been sent to \"$email\" successfully.";

        // Add success notifications
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                // Email success notification
                Swal.fire({
                    icon: 'success',
                    title: 'Email Sent Successfully',
                    text: 'Your diagnostic results have been sent to your email address.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true
                }).then(() => {
                    // Database success notification
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
            });
        </script>";

    } catch (Exception $e) {
        $message = "Failed to send the email. " . htmlspecialchars($e->getMessage());

        // Add error notifications
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Operation Failed',
                    text: 'Failed to send email and store data. Please try again later.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#d33',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            });
        </script>";
    } finally {
        Database::disconnect();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Nurse Diagnosis</title>

    <!-- Favicons -->
    <link href="img/logo.png" rel="icon">
    <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Marcellus:wght@400&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="vendor/aos/aos.css" rel="stylesheet">
    <link href="vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="css/main.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: url('microcity.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.8);
        }
        /* Loading Animation */
        .loading-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #22c55e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        .loading-text {
            font-size: 24px;
            color: #065f46;
            font-weight: bold;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Improved Result Display */
        .diagnosis-result {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .diagnosis-result.show {
            opacity: 1;
            transform: translateY(0);
        }
        .diagnosis-result h3 {
            color: #065f46;
            font-size: 28px;
            margin-bottom: 20px;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 10px;
        }
        .diagnosis-result p {
            font-size: 18px;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 16px;
        }
        /* iPad Optimizations */
        @media (min-width: 768px) and (max-width: 1024px) {
            .container {
                max-width: 90%;
                padding: 0 20px;
            }
            .vital-signs-container {
                padding: 24px;
            }
            .vital-signs-container .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }
            .diagnosis-result {
                padding: 32px;
            }
            .diagnosis-result h3 {
                font-size: 32px;
            }
            .diagnosis-result p {
                font-size: 20px;
            }
        }
        /* Nav menu as links only, no button style */
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
        .mobile-nav-toggle {
            font-size: 2rem;
            color: #222;
            cursor: pointer;
            display: none;
            background: none;
            border: none;
        }
        @media (max-width: 900px) {
            #navmenu ul {
                gap: 0.5rem;
                font-size: 0.95rem;
            }
            #navmenu ul li a {
                padding: 6px 10px;
                font-size: 0.95rem;
            }
        }
        .vital-signs-container {
            background: #ffffff;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .vital-signs-container h3 {
            font-size: 24px;
            text-align: center;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 16px;
        }
        .vital-signs-container .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .vital-signs-container .grid-item {
            font-size: 16px;
            font-weight: bold;
            color: #064e3b;
        }
        .diagnosis-button {
            display: block;
            margin: 20px auto;
            font-size: 20px;
            font-weight: bold;
            padding: 16px 32px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .diagnosis-button:hover {
            background-color: #218838;
        }
    </style>

    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <!-- Loading Animation Container -->
    <div class="loading-container" id="loadingContainer">
        <div class="loading-spinner"></div>
        <div class="loading-text">Generating AI Diagnosis...</div>
    </div>

    <div class="bg-overlay min-h-screen">
        
    <!-- Header Section -->
    <header id="header" class="header d-flex align-items-center position-relative">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.php" class="logo d-flex align-items-center">
                <img src="img/logo.png" alt="AI Vital">
            </a>

            <nav id="navmenu">
                <ul>
                    <li><a href="index.php" class="">Home</a></li>
                    <li><a href="registration2.php" class="">Registration</a></li>
                    <li><a href="userdata2.php" class="">User Data</a></li>
                    <li><a href="live reading.php" class="">Live-Reading</a></li>
                    <li><a href="about.php" class="">About Us</a></li>
                </ul>
            </nav>

        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-center text-3xl font-bold text-green-700 mb-6">AI Vital Diagnosis</h2>

        <!-- User Details Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h3 class="text-xl font-bold text-green-700 mb-4">User Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <p><strong>UID:</strong> <?php echo $uid; ?></p>
                <p><strong>Name:</strong> <?php echo $name ?: "N/A"; ?></p>
                <p><strong>Email:</strong> <?php echo $email ?: "N/A"; ?></p>
                <p><strong>Age:</strong> <?php echo $age; ?></p>
                <p><strong>Weight:</strong> <?php echo $weight; ?> kg</p>
                <p><strong>Height:</strong> <?php echo $height; ?> cm</p>
                <p><strong>Gender:</strong> <?php echo $gender; ?></p>
            </div>
        </div>

        <!-- Vital Signs Section -->
        <div class="vital-signs-container">
            <h3>Vital Results</h3>
            <div class="grid">
                <div class="grid-item">Body Temperature: <?php echo $body_temp; ?> °C</div>
                <div class="grid-item">ECG: <?php echo $ecg; ?> BPM</div>
                <div class="grid-item">Pulse Rate: <?php echo $pulse_rate; ?> BPM</div>
                <div class="grid-item">SpO₂: <?php echo $spo2; ?> %</div>
                <div class="grid-item">Blood Pressure: <?php echo $blood_pressure; ?> mmHg</div>
            </div>
        </div>

        <!-- Diagnosis Button -->
        <form action="" method="post">
            <button type="submit" class="diagnosis-button">Get AI Diagnosis</button>
        </form>

        <!-- Diagnosis Result -->
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST") : ?>
            <div class="diagnosis-result" id="diagnosisResult">
                <h3>AI Diagnosis Result:</h3>
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($diagnosis)); ?></p>
            </div>

            <!-- Email Button -->
            <div class="mt-6">
                <p class="text-green-700 font-bold"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Section -->
    <footer id="footer" class="footer dark-background">
        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6 footer-about">
                        <a href="index.php" class="logo d-flex align-items-center">
                            <span class="sitename">AI-VITAL</span>
                        </a>
                        <div class="footer-contact pt-3">
                            <p>MICROCITY OF BUSINESS AND TECHNOLOGY, INC.</p>
                            <p>Narra St., Capitol Drive, Tenejero, Balanga, Bataan </p>
                            <p class="mt-3"><strong>Phone:</strong> <span>(047-) 275-0786 / 09811865703</span></p>
                            <p><strong>Email:</strong> <span>info@microcitycomputercollege.com</span></p>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Start Using </h4>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="registration2.php">Registration</a></li>
                            <li><a href="userdata2.php">User Data</a></li>
                            <li><a href="live reading.php">Live-Reading</a></li>
                            <li><a href="about.php">About Us</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>What is?</h4>
                        <ul>
                            <li><a href="https://en.wikipedia.org/wiki/Blood_pressure" target="_blank">...Blood Pressure</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Human_body_temperature" target="_blank">...Body Temperature</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Electrocardiography" target="_blank">...Electrocardiogram</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Oxygen_saturation_(medicine)" target="_blank">...Oxygen Saturation (spO2)</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Pulse" target="_blank">...Pulse Rate</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Hardware Used</h4>
                        <ul>
                            <li><a href="#">MLX90614</a></li>
                            <li><a href="#">AD8232</a></li>
                            <li><a href="#">MAX30100</a></li>
                            <li><a href="#">MFRC522</a></li>
                            <li><a href="#">ESP-32 Wroom</a></li>
                            <li><a href="#">ESP-8266 </a></li>
                            <li><a href="#">Arduino Uno</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Tech Stack Used</h4>
                        <ul>
                            <li><a href="#">Languages: C++, Php, Javascript</a></li>
                            <li><a href="#">Frameworks/Libraries: Bootstrap, Tailwind CSS</a></li>
                            <li><a href="#">Data Base: MySQL</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="copyright text-center">
            <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">
                <div class="d-flex flex-column align-items-center align-items-lg-start">
                    <div>
                        © Copyright <strong><span>AI-Vital</span></strong>. All Rights Reserved
                    </div>
                    <div class="credits">
                        Designed by Einsbern</a>
                    </div>
                </div>

                <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
                    <a href="https://www.microcitycollege.com/" target="_blank"><i class="bi bi-browser-chrome"></i></a>
                    <a href="https://www.facebook.com/microcity.balanga" target="_blank"><i class="bi bi-facebook"></i></a>
                    <a href="mailto:einsbernsystem@gmail.com?subject=Send%20Feedback%20to%20Developer"><i class="bi bi-envelope"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Add JavaScript for loading animation and result display -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const loadingContainer = document.getElementById('loadingContainer');
            const diagnosisResult = document.getElementById('diagnosisResult');

            if (form) {
                form.addEventListener('submit', function() {
                    loadingContainer.style.display = 'flex';
                });
            }

            if (diagnosisResult) {
                // Show the result with animation after a short delay
                setTimeout(() => {
                    diagnosisResult.classList.add('show');
                }, 100);
            }
        });
    </script>
</body>
</html>