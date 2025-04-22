<?php
$uid = "bd1824";
$name = "JOHN PAUL LEGASPI";
$age = 24;
$weight = 80;
$height = 156;
$gender = "Male";
$temperature = "36.50 °C";
$ecg = "98.00";
$pulse_rate = "75 BPM";
$spo2 = "98.00 %";
$blood_pressure = "120/80 mmHg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Nurse Diagnoser</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .transition-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.8s ease-in-out;
        }
        .move-left {
            transform: translateX(-150px);
        }
        .fade-in {
            opacity: 0;
            animation: fadeIn 1s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-900 text-white p-8 flex items-center justify-center h-screen">
    <div id="main-container" class="flex w-full max-w-6xl justify-center">
        <div id="left-container" class="transition-container space-y-6 transition-transform">
            <div id="user-info" class="bg-gray-800 p-6 rounded-2xl shadow-lg border border-blue-400">
                <h2 class="text-xl font-semibold mb-4 text-blue-300">User Details</h2>
                <div class="grid grid-cols-2 gap-4 text-lg">
                    <p><strong>UID:</strong> <?php echo $uid; ?></p>
                    <p><strong>Name:</strong> <?php echo $name; ?></p>
                    <p><strong>Age:</strong> <?php echo $age; ?></p>
                    <p><strong>Weight:</strong> <?php echo $weight; ?> kg</p>
                    <p><strong>Height:</strong> <?php echo $height; ?> cm</p>
                    <p><strong>Gender:</strong> <?php echo $gender; ?></p>
                </div>
            </div>
        
            <div id="vital-signs" class="grid grid-cols-2 gap-6 w-full">
                <div class="bg-gray-800 p-4 rounded-2xl shadow-lg border border-red-400 text-center">
                    <p class="text-lg text-red-300">Body Temperature</p>
                    <p class="text-2xl font-bold text-red-500"> <?php echo $temperature; ?></p>
                </div>
                <div class="bg-gray-800 p-4 rounded-2xl shadow-lg border border-yellow-400 text-center">
                    <p class="text-lg text-yellow-300">ECG</p>
                    <p class="text-2xl font-bold text-yellow-500"> <?php echo $ecg; ?></p>
                </div>
                <div class="bg-gray-800 p-4 rounded-2xl shadow-lg border border-green-400 text-center">
                    <p class="text-lg text-green-300">Pulse Rate</p>
                    <p class="text-2xl font-bold text-green-500"> <?php echo $pulse_rate; ?></p>
                </div>
                <div class="bg-gray-800 p-4 rounded-2xl shadow-lg border border-blue-400 text-center">
                    <p class="text-lg text-blue-300">SpO₂</p>
                    <p class="text-2xl font-bold text-blue-500"> <?php echo $spo2; ?></p>
                </div>
                <div class="bg-gray-800 p-4 rounded-2xl shadow-lg border border-purple-400 text-center col-span-2">
                    <p class="text-lg text-purple-300">Blood Pressure</p>
                    <p class="text-2xl font-bold text-purple-500"> <?php echo $blood_pressure; ?></p>
                </div>
            </div>
            
            <button id="diagnose-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl shadow-md transition-transform transform hover:scale-105">
                Get Diagnosis
            </button>
        </div>
    
        <div id="diagnosis-result" class="hidden w-1/2 ml-10 bg-gray-800 p-6 rounded-2xl shadow-lg border border-green-400 text-lg leading-relaxed">
            <p class="text-green-300 text-xl font-bold">🩺 AI Nurse Diagnosis:</p>
            <p>Hello there! As your virtual nurse, I'm happy to help you assess your vital signs and provide guidance on what they might mean for your health.</p>
            <ul class="list-disc pl-6 mt-2">
                <li>Body Temperature: 36.5 °C (slightly below normal range)</li>
                <li>ECG Rate: 98 BPM (normal range)</li>
                <li>Pulse Rate: 75 BPM (slightly above normal range)</li>
                <li>SpO₂ Level: 98 % (normal range)</li>
                <li>Blood Pressure: 120/80 mmHg (borderline high)</li>
            </ul>
            <p class="mt-4">Overall, your vitals are mostly normal but with a few areas to monitor.</p>
            <p class="mt-4">Stay hydrated, practice relaxation techniques, and maintain a healthy diet!</p>
            <p class="mt-4 font-bold">For any concerns, consult a healthcare professional.</p>
        </div>
    </div>
    
    <script>
        $("#diagnose-btn").click(function() {
            $("#left-container").addClass("move-left");
            setTimeout(function() {
                $("#diagnosis-result").removeClass("hidden").addClass("fade-in");
            }, 800);
        });
    </script>
</body>
</html>
