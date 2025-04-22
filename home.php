<?php
//?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title>VitaSign: A Diagnostics System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        fadeIn: 'fadeIn 1.5s ease-in-out',
                        slideUp: 'slideUp 1.5s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
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
        }
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.8); /* White overlay with reduced opacity */
        }
    </style>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <div class="bg-overlay min-h-screen">
        <nav class="bg-green-700 text-white shadow-lg">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="hover:bg-green-600 rounded-full p-2 transition-colors duration-200">
                        <img src="icon.png" alt="System Icon" class="w-10 h-10 hover:opacity-80 transition-opacity duration-200">
                    </a>
                    <a href="index.php" class="text-xl font-bold hover:text-green-300 hover:bg-green-600 px-2 py-1 rounded-md transition-colors duration-200">AI-VITAL</a>
                </div>
                <div class="flex space-x-4">
                    <a href="index.php" class="px-3 py-2 rounded-md text-sm font-medium bg-black">Home</a>
                    <a href="registration2.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-green-600">Register</a>
                    <a href="userdata2.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-green-600">User Data</a>
                    <a href="live reading.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-green-600">Live-Reading</a>
                    <a href="results2.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-green-600">Results</a>
                    <a href="about.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-green-600">About Us</a>
                </div>
            </div>
        </nav>

        <main class="flex-grow container mx-auto px-4 py-8">
            <div class="bg-white shadow-lg rounded-lg p-6 text-center animate-fadeIn">
                <h1 class="text-3xl font-bold text-green-700 mb-4">Welcome to AI-VITAL</h1>
                <p class="text-gray-700 mb-6">
                    Advanced real-time monitoring of vital signs with seamless data acquisition and secure MySQL integration, ensuring accurate health tracking and efficient storage for comprehensive analysis.
                </p>
            </div>

            <div class="mt-8">
                <h2 class="text-2xl font-semibold text-green-700 mb-4 animate-slideUp">AI-VITAL: A Smart System for Vital Signs Monitoring and Diagnosis</h2>
                <p class="text-gray-700 mb-4 text-justify animate-slideUp">
                    Einsbern System presents an innovative Healthcare Diagnostic and Monitoring System, designed to provide real-time health tracking using cutting-edge sensor technology and artificial intelligence. Our system captures and analyzes vital signs, including body temperature, ECG readings, pulse rate, oxygen saturation (SpO2), and blood pressure, using a sphygmomanometer.
                </p>
                <h3 class="text-xl font-semibold text-green-600 mb-2 animate-slideUp">How It Works</h3>
                <ul class="list-disc list-inside text-gray-700 mb-4 animate-slideUp">
                    <li><strong>Body Temperature</strong> – Monitored using an infrared temperature sensor (MLX90614) for accurate fever detection.</li>
                    <li><strong>Oxygen Saturation (SpO2) & Pulse Rate</strong> – Tracked using a pulse oximeter (MAX30100) to assess oxygen levels and heart rate.</li>
                    <li><strong>Electrocardiogram (ECG) Readings</strong> – Captured through an ECG sensor, allowing early detection of cardiac irregularities.</li>
                    <li><strong>Blood Pressure</strong> – Measured using a sphygmomanometer, a critical indicator of heart health.</li>
                </ul>
                <p class="text-gray-700 mb-4 text-justify animate-slideUp">
                    Each user is identified using RFID technology, allowing for seamless and automatic health record retrieval. The collected data is securely transmitted to a centralized web-based database, where it is processed and analyzed by our AI-powered diagnostic system to provide real-time health insights.
                </p>
                <h3 class="text-xl font-semibold text-green-600 mb-2 animate-slideUp">Key Features & Benefits</h3>
                <ul class="list-disc list-inside text-gray-700 mb-4 animate-slideUp">
                    <li><strong>Comprehensive Health Monitoring</strong> – Tracks body temperature, SpO2, pulse rate, ECG readings, and blood pressure for a complete health assessment.</li>
                    <li><strong>AI-Powered Diagnosis</strong> – Analyzes data to detect potential health issues and provides instant diagnostic results.</li>
                    <li><strong>RFID Integration</strong> – Ensures secure and accurate user identification.</li>
                    <li><strong>Secure Database</strong> – Stores data in an encrypted cloud-based database for privacy and accessibility.</li>
                    <li><strong>User-Friendly Interface</strong> – Intuitive dashboard for real-time insights and historical data.</li>
                    <li><strong>Automated Health Assessments</strong> – Provides faster, more accurate diagnostics, reducing the burden on healthcare providers.</li>
                </ul>
            </div>
        </main>

        <?php include 'footer.php'; ?>
    </div>
</body>
</html>