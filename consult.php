<?php
// Get current date for fallback purposes
$currentDate = date("F j, Y");

// Initialize AI responses array
$ai_responses = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultInput'])) {
    $userInput = trim($_POST['consultInput']);

    $prompts = [
        "Thank you for sharing your symptoms. Based on your input: \"$userInput\", we recommend monitoring your condition and seeing a healthcare professional if it worsens.",
        "We’ve received your description: \"$userInput\". It seems like you might be experiencing symptoms related to [condition]. We suggest further investigation and consultation with a doctor.",
        "Thank you for your input: \"$userInput\". We understand your symptoms are [severity]. Please note that our AI is learning your data and may offer insights as it analyzes more cases.",
        "We’ve logged your symptoms: \"$userInput\". It’s important to rest and stay hydrated. If symptoms persist or worsen, please consult a medical professional.",
        "Your symptoms have been noted: \"$userInput\". You may want to track them over the next few days and see if they follow any particular pattern. Let us know if you experience any changes.",
        "Thank you for your description: \"$userInput\". We recommend watching for additional symptoms. If you have other concerns, feel free to update us at any time.",
        "Your input has been received: \"$userInput\". Based on your symptoms, it’s possible that [condition] could be a factor. Please monitor your condition and consult a healthcare provider if necessary.",
        "We’ve logged your symptoms: \"$userInput\". It’s important to track how they evolve. If there’s a drastic change, please seek medical attention.",
        "Thank you for providing your health details: \"$userInput\". Based on our system’s analysis, we recommend further action based on your specific condition. Please consult with a specialist if needed.",
        "Your health information has been successfully recorded: \"$userInput\". We’ll continue learning and provide more tailored advice as more data comes in. Stay safe and don’t hesitate to ask for further help!"
    ];

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
            file_put_contents('cohere_debug.log', $result . PHP_EOL, FILE_APPEND);

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
            position: fixed; /* Fix the marquee at the bottom of the viewport */
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }
        .marquee span {
            display: inline-block;
            animation: scrollLeft 40s linear infinite; /* Slower scrolling */
        }
        footer {
            margin-bottom: 60px; /* Add space above the marquee */
        }
        @keyframes scrollLeft {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(-100%);
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
    </script>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <div class="bg-overlay min-h-screen">
        
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

        <div class="container mx-auto px-4 py-8 flex space-x-8">
            <!-- Input box and submit button panel -->
            <div class="readings-panel flex-grow flex flex-col items-center justify-center">
                <form action="" method="post" class="w-full flex flex-col items-center">
                    <label for="consultInput" class="block text-2xl font-bold text-green-900 mb-4">Describe your symptoms or concerns:</label>
                    <textarea id="consultInput" name="consultInput" rows="10" class="w-full max-w-2xl p-4 border-2 border-green-400 rounded-lg text-lg mb-6" placeholder="Type your message here..."><?php echo isset($_POST['consultInput']) ? htmlspecialchars($_POST['consultInput']) : ''; ?></textarea>
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-green-500 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-green-700 transition">Submit</button>
                        <a href="live reading.php" class="bg-gray-400 text-white px-8 py-3 rounded-md text-xl font-semibold hover:bg-gray-600 transition flex items-center justify-center">Back</a>
                    </div>
                </form>
            </div>
            <!-- User Details Panel -->
            <div class="user-panel w-1/3">
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
        <!-- AI Consultation Results in a separate container below the main flex container -->
        <?php if (!empty($ai_responses)): ?>
        <div id="ai-result" class="container mx-auto px-4 mt-10 mb-10">
            <div class="w-full max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-green-800 mb-4">AI Consultation Results</h3>
                <ol class="list-decimal pl-6 space-y-2">
                    <?php foreach ($ai_responses as $idx => $resp): ?>
                        <li>
                            <strong>Prompt <?= $idx + 1 ?>:</strong>
                            <pre class="whitespace-pre-wrap break-words"><?= htmlspecialchars($resp) ?></pre>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <script>
            // Auto-scroll to the result after submission
            window.onload = function() {
                var result = document.getElementById('ai-result');
                if (result) {
                    result.scrollIntoView({ behavior: 'smooth' });
                }
            };
        </script>
        <?php endif; ?>
    </div>

    <!-- Scrolling Marquee -->
    <div class="marquee">
        <span>To use AI-VITAL, first tap your RFID card provided by the school nurse on the scanner and complete the registration process. Once registered, tap your RFID again to log in. Secure the blood pressure (BP) strap on your upper arm, close to your heart. Attach the electrode pads to your chest as instructed. Ensure the pulse oximeter is clipped onto your finger. Wait for the readings to stabilize and view your results on the screen. For further assistance, contact the school nurse.</span>
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
                            <li><a href="results2.php">Results</a></li>
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
                            <li><a href="#">AD8323</a></li>
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
                        © Copyright <strong><span>AI-VITAL</span></strong>. All Rights Reserved
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

    <script>
        async function fetchSensorData() {
            try {
                const response = await fetch("fetch_data.php"); // Change to fetch_data.php for ESP data change to .sample
                const data = await response.json();

                // Debugging: Log the fetched data
                console.log("Fetched Sensor Data:", data);

                // Update the UI with fetched sensor data
                document.getElementById("temp").innerText = data.body_temp !== null ? parseFloat(data.body_temp).toFixed(2) + " °C" : "0.00 °C";
                document.getElementById("ecg").innerText = data.ecg !== null ? parseFloat(data.ecg).toFixed(2) : "0.00";
                document.getElementById("pulse_rate").innerText = data.pulse_rate !== null ? parseInt(data.pulse_rate, 10) + " BPM" : "0 BPM";
                document.getElementById("spo2").innerText = data.spo2 !== null ? parseFloat(data.spo2).toFixed(2) + " %" : "0.00 %";
                document.getElementById("bp").innerText = data.bp || "N/A mmHg";

                // Populate hidden email form fields
                document.getElementById("emailBodyTemp").value = data.body_temp || "0.00";
                document.getElementById("emailEcg").value = data.ecg || "0.00";
                document.getElementById("emailPulseRate").value = data.pulse_rate || "0";
                document.getElementById("emailSpo2").value = data.spo2 || "0.00";
                document.getElementById("emailBp").value = data.bp || "N/A";
            } catch (error) {
                console.error("Error fetching sensor data:", error);
            }
        }

        async function fetchUID() {
            try {
                const response = await fetch("UIDContainer.php", { cache: "no-store" });
                const uid = (await response.text()).trim();
                document.getElementById("uid").innerText = uid || "N/A";

                // Show or hide the "My Results" button based on UID
                const myResultsButton = document.getElementById("myResultsButton");
                if (uid) {
                    myResultsButton.style.display = "block";
                } else {
                    myResultsButton.style.display = "none";
                }

                if (uid) {
                    const userResponse = await fetch(`handle_rfid.php?uid=${encodeURIComponent(uid)}`, { cache: "no-store" });
                    const userData = await userResponse.json();

                    if (!userData.error) {
                        document.getElementById("name").innerText = userData.name || "N/A";
                        document.getElementById("email").innerText = userData.email || "N/A";
                        document.getElementById("age").innerText = userData.age ? `${userData.age} years old` : "N/A";
                        document.getElementById("weight").innerText = userData.weight ? `${userData.weight} kg` : "N/A";
                        document.getElementById("height").innerText = userData.height ? `${userData.height} cm` : "N/A";
                        document.getElementById("gender").innerText = userData.gender || "N/A";

                        // Populate hidden email form fields
                        document.getElementById("emailUid").value = uid;
                        document.getElementById("emailName").value = userData.name || "N/A";
                        document.getElementById("emailEmail").value = userData.email || "N/A";
                        document.getElementById("emailAge").value = userData.age || "N/A";
                        document.getElementById("emailWeight").value = userData.weight || "N/A";
                        document.getElementById("emailHeight").value = userData.height || "N/A";
                        document.getElementById("emailGender").value = userData.gender || "N/A";
                    }
                }
            } catch (error) {
                console.error("Error fetching UID or user details:", error);
            }
        }

        function updateDeviceTime() {
            const now = new Date();
            const formattedTime = now.toLocaleString();
            document.getElementById("currentTime").innerText = formattedTime;
        }

        setInterval(fetchSensorData, 2000); // Fetch sensor data every 2 seconds
        setInterval(fetchUID, 300); // Fetch UID every 300ms
        setInterval(updateDeviceTime, 1000); // Update time every second
    </script>
</body>
</html>