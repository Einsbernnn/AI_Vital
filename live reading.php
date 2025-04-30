<?php
// Clear UID on page load
$Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);

// Get current date for fallback purposes
$currentDate = date("F j, Y");
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
        .diagnosis-modal-content {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18);
            max-width: 700px;
            width: 96vw;
            min-width: 340px;
            min-height: 260px;
            padding: 36px 38px 28px 38px;
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            /* Rectangle landscape tab */
            border-radius: 18px;
        }
        #diagnosisModal {
            background: rgba(0,0,0,0.25) !important;
            display: none;
            position: fixed;
            z-index: 2000;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            align-items: center; justify-content: center;
        }
        .diagnosis-modal-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            white-space: nowrap;
        }
        .diagnosis-modal-header .bi-robot {
            font-size: 2.8rem;
            vertical-align: middle;
            flex-shrink: 0;
        }
        .diagnosis-modal-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: #065f46;
            line-height: 1.1;
            flex-shrink: 1;
        }
        .diagnosis-modal-content .sensor-indicators {
            display: flex;
            justify-content: flex-start;
            gap: 32px;
            margin-bottom: 10px;
            flex-wrap: nowrap;
            width: 100%;
            overflow-x: auto;
        }
        .diagnosis-modal-content .sensor-indicators > div {
            min-width: 90px;
            max-width: 120px;
        }
        @media (max-width: 900px) {
            .diagnosis-modal-content {
                max-width: 99vw;
                padding: 18px 2vw 18px 2vw;
            }
            .diagnosis-modal-content .sensor-indicators {
                gap: 16px;
            }
        }
        @media (max-width: 600px) {
            .diagnosis-modal-content {
                max-width: 99vw;
                min-width: 0;
                padding: 10px 1vw 10px 1vw;
            }
            .diagnosis-modal-header .diagnosis-modal-title {
                font-size: 1.2rem;
            }
            .diagnosis-modal-header .bi-robot {
                font-size: 1.7rem;
            }
            .diagnosis-modal-content .sensor-indicators > div {
                min-width: 70px;
                max-width: 100px;
            }
        }
    </style>
    <script src="jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            let lastUID = ""; // Store the last fetched UID to avoid unnecessary updates

            function fetchUID() {
                $.get("UIDContainer.php", { cache: "no-store" }, function(data) {
                    const uid = data.trim();
                    console.log("Fetched UID:", uid);

                    // Update the UI only if the UID has changed
                    if (uid && uid !== lastUID) {
                        lastUID = uid; // Update the lastUID variable
                        $("#uid").text(uid);
                        $("#name").text("Loading...");
                        $("#email").text("Loading...");
                        $("#age").text("Loading...");
                        $("#weight").text("Loading...");
                        $("#height").text("Loading...");
                        $("#gender").text("Loading...");

                        // Fetch user details based on the new UID
                        fetchUserDetails(uid);
                    } else if (!uid) {
                        // Clear the UI if no UID is detected
                        lastUID = ""; // Reset the lastUID variable
                        clearUserDetails();
                    }
                });
            }

            function fetchUserDetails(uid) {
                $.get(`handle_rfid.php?uid=${encodeURIComponent(uid)}`, { cache: "no-store" })
                    .done(function(data) {
                        let userData;
                        try {
                            userData = typeof data === "object" ? data : JSON.parse(data);
                        } catch (error) {
                            userData = {};
                        }
                        console.log("User data received:", userData);

                        if (userData && userData.error === 'no_match') {
                            clearUserDetails(true); // true = clear UID after alert
                            showNoMatchAlert();
                        } else if (userData && !userData.error) {
                            // Valid user data found - update UI
                            $("#name").text(userData.name || "N/A");
                            $("#email").text(userData.email || "N/A");
                            $("#age").text(userData.age ? `${userData.age} years old` : "N/A");
                            $("#weight").text(userData.weight ? `${userData.weight} kg` : "N/A");
                            $("#height").text(userData.height ? `${userData.height} cm` : "N/A");
                            $("#gender").text(userData.gender || "N/A");

                            // Populate hidden email form fields
                            $("#emailUid").val(uid);
                            $("#emailName").val(userData.name || "N/A");
                            $("#emailEmail").val(userData.email || "N/A");
                            $("#emailAge").val(userData.age || "N/A");
                            $("#emailWeight").val(userData.weight || "N/A");
                            $("#emailHeight").val(userData.height || "N/A");
                            $("#emailGender").val(userData.gender || "N/A");

                            // Show welcome SweetAlert
                            Swal.fire({
                                icon: 'success',
                                title: `Welcome ${userData.name || "User"}!`,
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                        }
                    })
                    .fail(function() {
                        clearUserDetails(true);
                        showNoMatchAlert();
                    });
            }

            function showNoMatchAlert() {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Match Found',
                    text: 'Please Register Your Card First To Continue Using AI Vital.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#3085d6',
                    timer: 5000, // Auto-dismiss after 5 seconds
                    timerProgressBar: true,
                    didClose: () => clearUserDetails(true) // Clear UID after alert timer ends
                });
            }

            function clearUserDetails(clearUidFile = false) {
                // Clear the UI
                $("#uid").text("N/A");
                $("#name").text("N/A");
                $("#email").text("N/A");
                $("#age").text("N/A");
                $("#weight").text("N/A");
                $("#height").text("N/A");
                $("#gender").text("N/A");

                // Clear the UID in UIDContainer.php if requested
                if (clearUidFile) {
                    $.post("UIDContainer.php", { clear: true }, function() {
                        console.log("UID cleared in UIDContainer.php");
                    }).fail(function() {
                        console.error("Failed to clear UID in UIDContainer.php");
                    });
                }
            }

            fetchUID(); // Fetch UID immediately on page load
            setInterval(fetchUID, 300); // Fetch UID every 300 milliseconds
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Utility: Build sensor summary string for consult
    function buildSensorSummary() {
        const temp = document.getElementById("temp").innerText.trim();
        const ecg = document.getElementById("ecg").innerText.trim();
        const pulse = document.getElementById("pulse_rate").innerText.trim();
        const spo2 = document.getElementById("spo2").innerText.trim();
        const bp = document.getElementById("bp").innerText.trim();
        return `Temp\n${temp}\nECG\n${ecg}\nPulse\n${pulse}\nSpO₂\n${spo2}\nBP\n${bp}`;
    }

    // Reusable diagnosis modal logic
    function showDiagnosisModal(validMap, onSuccess) {
        const modal = document.getElementById("diagnosisModal");
        modal.style.display = "flex";
        const sensors = ["temp","ecg","pulse","spo2","bp"];
        const sensorLabels = {
            temp: "Body Temperature",
            ecg: "ECG",
            pulse: "Pulse Rate",
            spo2: "SpO₂",
            bp: "Blood Pressure"
        };
        const iconIds = {
            temp: "indicator-temp-icon",
            ecg: "indicator-ecg-icon",
            pulse: "indicator-pulse-icon",
            spo2: "indicator-spo2-icon",
            bp: "indicator-bp-icon"
        };
        const values = {
            temp: document.getElementById("temp").innerText,
            ecg: document.getElementById("ecg").innerText,
            pulse: document.getElementById("pulse_rate").innerText,
            spo2: document.getElementById("spo2").innerText,
            bp: document.getElementById("bp").innerText
        };
        let anyInvalid = false;
        let invalidSensors = [];
        sensors.forEach(s => {
            document.getElementById(`indicator-${s}-status`).innerHTML = '<span class="sensor-loader"></span>';
            document.getElementById(`indicator-${s}-text`).textContent = "Processing…";
            document.getElementById(`indicator-${s}-text`).style.color = "#666";
            document.getElementById(`indicator-${s}-value`).textContent = "";
            document.getElementById(`indicator-${s}-value`).classList.remove("sensor-value-animate");
            document.getElementById(iconIds[s]).style.color = "#888";
        });
        document.getElementById("diagnosisProgressBar").style.width = "0%";
        document.getElementById("diagnosisProgressText").textContent = "Validating sensors...";

        let idx = 0;
        const keys = ["temp","ecg","pulse","spo2","bp"];
        function step() {
            if (idx < keys.length) {
                const key = keys[idx];
                setTimeout(() => {
                    document.getElementById(`indicator-${key}-status`).innerHTML = validMap[key] ? "✔️" : "❌";
                    const valueEl = document.getElementById(`indicator-${key}-value`);
                    valueEl.textContent = values[key] || "";
                    valueEl.classList.add("sensor-value-animate");
                    setTimeout(() => valueEl.classList.remove("sensor-value-animate"), 600);
                    if (validMap[key]) {
                        document.getElementById(`indicator-${key}-text`).textContent = "Valid Reading";
                        document.getElementById(`indicator-${key}-text`).style.color = "#22c55e";
                        document.getElementById(iconIds[key]).style.color = "#22c55e";
                    } else {
                        document.getElementById(`indicator-${key}-text`).textContent = "Invalid: Value is N/A";
                        document.getElementById(`indicator-${key}-text`).style.color = "#ef4444";
                        document.getElementById(iconIds[key]).style.color = "#ef4444";
                        anyInvalid = true;
                        invalidSensors.push(sensorLabels[key]);
                    }
                    document.getElementById("diagnosisProgressBar").style.width = (((idx + 1) / keys.length) * 100) + "%";
                    idx++;
                    step();
                }, 1000);
            } else {
                setTimeout(() => {
                    document.getElementById("diagnosisProgressText").textContent = "Diagnosis ready! Please review and continue.";
                    document.getElementById("diagnosisProgressBar").style.width = "100%";
                    var waitMsg = document.querySelector('#diagnosisModal span[style*="color:#888"]');
                    if (waitMsg) waitMsg.style.display = "none";
                    // Play notification sound
                    try {
                        let audio = document.getElementById('diagnosisReadyAudio');
                        if (!audio) {
                            audio = document.createElement('audio');
                            audio.id = 'diagnosisReadyAudio';
                            audio.src = 'https://cdn.pixabay.com/audio/2022/07/26/audio_124bfa4c82.mp3';
                            audio.preload = 'auto';
                            document.body.appendChild(audio);
                        }
                        audio.currentTime = 0;
                        audio.play();
                    } catch (e) {}
                    if (window.navigator && window.navigator.vibrate) {
                        window.navigator.vibrate([100, 30, 100]);
                    }
                    setTimeout(() => {
                        if (!anyInvalid) {
                            document.getElementById("diagnosisProceedContainer").style.display = "flex";
                            document.getElementById("diagnosisProceedBtn").onclick = function() {
                                modal.style.display = "none";
                                if (typeof onSuccess === "function") onSuccess();
                            };
                        } else {
                            modal.style.display = "none";
                            Swal.fire({
                                icon: 'warning',
                                title: 'Missing/Invalid Readings',
                                text: "The following readings are missing or invalid: " + invalidSensors.join(", ") + ". Please check all sensors and try again.",
                                confirmButtonText: 'Okay',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    }, 6000);
                }, 500);
            }
        }
        step();
    }

    // Diagnosis button (AI Diagnosis)
    document.getElementById("diagnosisButton").addEventListener("click", function (event) {
        const uid = document.getElementById("uid").innerText.trim();
        const temp = document.getElementById("temp").innerText.trim();
        const ecg = document.getElementById("ecg").innerText.trim();
        const pulseRate = document.getElementById("pulse_rate").innerText.trim();
        const spo2 = document.getElementById("spo2").innerText.trim();
        const bp = document.getElementById("bp").innerText.trim();

        if (!uid || uid === "N/A") {
            event.preventDefault();
            var modal = document.getElementById("diagnosisModal");
            if (modal) modal.style.display = "none";
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
        event.preventDefault();
        var validMap = {
            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
        };
        showDiagnosisModal(validMap, function() {
            document.getElementById("sendToMailForm").submit();
        });
    });

    // Consult button (redirect to consult.php with sensor summary)
    document.getElementById("consultButton").addEventListener("click", function (event) {
        const uid = document.getElementById("uid").innerText.trim();
        const temp = document.getElementById("temp").innerText.trim();
        const ecg = document.getElementById("ecg").innerText.trim();
        const pulseRate = document.getElementById("pulse_rate").innerText.trim();
        const spo2 = document.getElementById("spo2").innerText.trim();
        const bp = document.getElementById("bp").innerText.trim();

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
        event.preventDefault();
        var validMap = {
            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
        };
        showDiagnosisModal(validMap, function() {
            // Build sensor summary and redirect to consult.php with it as a GET param
            const summary = encodeURIComponent(buildSensorSummary());
            window.location.href = "consult.php?sensor_summary=" + summary;
        });
    });

    // Close button logic
    document.getElementById("diagnosisModalClose").onclick = function() {
        document.getElementById("diagnosisModal").style.display = "none";
    };
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
            <!-- Real-Time Sensor Readings Panel -->
            <div class="readings-panel flex-grow">
                <h3>Real-Time Vital Readings</h3>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="grid-item red">
                        <p class="label" style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            Body Temperature
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;"
                                onclick="toggleInfo('bodyTempInfo', event)"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </p>
                        <div id="bodyTempInfo" class="sensor-tooltip" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1fae5;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:0.98rem;max-width:220px;">
                            Normal: 36.1°C – 37.2°C<br>Normal body temperature ranges from 36.5°C to 37.5°C. Below or above may indicate health issues.
                        </div>
                        <p class="value" id="temp">0.00 °C</p>
                    </div>
                    <div class="grid-item yellow">
                        <p class="label" style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            ECG
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;"
                                onclick="toggleInfo('ecgInfo', event)"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </p>
                        <div id="ecgInfo" class="sensor-tooltip" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1fae5;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:0.98rem;max-width:220px;">
                            Normal: 60–100 BPM, regular rhythm<br>ECG measures heart electrical activity. Normal resting heart activity is steady and consistent.
                        </div>
                        <p class="value" id="ecg">0.00</p>
                    </div>
                    <div class="grid-item green">
                        <p class="label" style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            Pulse Rate
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;"
                                onclick="toggleInfo('pulseInfo', event)"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </p>
                        <div id="pulseInfo" class="sensor-tooltip" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1fae5;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:0.98rem;max-width:220px;">
                            Normal: 60–100 BPM<br>Normal pulse rate for adults is 60–100 BPM (beats per minute). Higher or lower may indicate a condition.
                        </div>
                        <p class="value" id="pulse_rate">0 BPM</p>
                    </div>
                    <div class="grid-item blue">
                        <p class="label" style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            SpO₂
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;"
                                onclick="toggleInfo('spo2Info', event)"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </p>
                        <div id="spo2Info" class="sensor-tooltip" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1fae5;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:0.98rem;max-width:220px;">
                            Normal: 95% – 100%<br>Normal SpO₂ levels are 95–100%. Values below 90% may require medical attention.
                        </div>
                        <p class="value" id="spo2">0.00 %</p>
                    </div>
                    <div class="grid-item purple col-span-2">
                        <p class="label" style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            Blood Pressure
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;"
                                onclick="toggleInfo('bpInfo', event)"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </p>
                        <div id="bpInfo" class="sensor-tooltip" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1fae5;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:0.98rem;max-width:220px;">
                            Normal: 90/60 mmHg – 120/80 mmHg<br>Ideal blood pressure is around 120/80 mmHg. High or low readings can be a sign of cardiovascular issues.
                        </div>
                        <p class="value" id="bp">N/A mmHg</p>
                    </div>
                </div>
                <div class="mt-4 flex justify-center space-x-4">
                    <button id="quickSaveButton" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Quick Save</button>
                    <form action="AI_send.php" method="get" id="sendToMailForm">
                        <input type="hidden" name="uid" id="emailUid">
                        <input type="hidden" name="name" id="emailName">
                        <input type="hidden" name="email" id="emailEmail">
                        <input type="hidden" name="age" id="emailAge">
                        <input type="hidden" name="weight" id="emailWeight">
                        <input type="hidden" name="height" id="emailHeight">
                        <input type="hidden" name="gender" id="emailGender">
                        <input type="hidden" name="body_temperature" id="emailBodyTemp">
                        <input type="hidden" name="ecg" id="emailEcg">
                        <input type="hidden" name="pulse_rate" id="emailPulseRate">
                        <input type="hidden" name="spo2" id="emailSpo2">
                        <input type="hidden" name="blood_pressure" id="emailBp">
                        <button type="submit" id="diagnosisButton" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Get AI Diagnosis</button>
                    </form>
                    <button id="consultButton" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Consult</button>
                </div>
                <!-- Diagnosis Loading Modal Overlay -->
                <style>
                .sensor-loader {
                    width: 36px;
                    height: 36px;
                    border: 4px solid #d1fae5;
                    border-top: 4px solid #22c55e;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-bottom: 2px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg);}
                    100% { transform: rotate(360deg);}
                }
                </style>
                <div id="diagnosisModal" style="display:none; position:fixed; z-index:2000; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.25); align-items:center; justify-content:center;">
                    <div class="diagnosis-modal-content" style="position:relative;">
                        <!-- Close Button -->
                        <button id="diagnosisModalClose" type="button" style="position:absolute;top:16px;right:16px;background:transparent;border:none;font-size:2rem;line-height:1;color:#888;cursor:pointer;z-index:10;" aria-label="Close">&times;</button>
                        <div class="diagnosis-modal-header">
                            <i class="bi bi-robot"></i>
                            <span class="diagnosis-modal-title">Generating Diagnosis Report</span>
                        </div>
                        <div style="width:100%; max-width:600px; margin:0 auto 24px;">
                            <div style="background:#d1fae5; border-radius:8px; overflow:hidden;">
                                <div id="diagnosisProgressBar" style="height:18px; width:0%; background:#22c55e; transition:width 0.5s;"></div>
                            </div>
                            <div style="font-size:1rem; color:#065f46; margin-top:6px;" id="diagnosisProgressText">Validating sensors...</div>
                        </div>
                        <div class="sensor-indicators">
                            <div id="indicator-temp" style="display:flex; flex-direction:column; align-items:center;">
                                <span class="bi bi-thermometer" id="indicator-temp-icon" style="font-size:2rem; color:#888;"></span>
                                <span style="font-size:0.95rem;">Temp</span>
                                <span id="indicator-temp-status">
                                    <span class="sensor-loader"></span>
                                </span>
                                <span id="indicator-temp-value" style="font-size:1.05rem; color:#333; margin-top:2px;"></span>
                                <span id="indicator-temp-text" style="font-size:0.92rem; color:#666; text-align:center; word-break:break-word;"></span>
                                <span id="indicator-temp-help" style="display:none; color:#ef4444; font-size:0.92rem; margin-top:2px;"></span>
                            </div>
                            <div id="indicator-ecg" style="display:flex; flex-direction:column; align-items:center;">
                                <span class="bi bi-activity" id="indicator-ecg-icon" style="font-size:2rem; color:#888;"></span>
                                <span style="font-size:0.95rem;">ECG</span>
                                <span id="indicator-ecg-status">
                                    <span class="sensor-loader"></span>
                                </span>
                                <span id="indicator-ecg-value" style="font-size:1.05rem; color:#333; margin-top:2px;"></span>
                                <span id="indicator-ecg-text" style="font-size:0.92rem; color:#666; text-align:center; word-break:break-word;"></span>
                                <span id="indicator-ecg-help" style="display:none; color:#ef4444; font-size:0.92rem; margin-top:2px;"></span>
                            </div>
                            <div id="indicator-pulse" style="display:flex; flex-direction:column; align-items:center;">
                                <span class="bi bi-heart-pulse" id="indicator-pulse-icon" style="font-size:2rem; color:#888;"></span>
                                <span style="font-size:0.95rem;">Pulse</span>
                                <span id="indicator-pulse-status">
                                    <span class="sensor-loader"></span>
                                </span>
                                <span id="indicator-pulse-value" style="font-size:1.05rem; color:#333; margin-top:2px;"></span>
                                <span id="indicator-pulse-text" style="font-size:0.92rem; color:#666; text-align:center; word-break:break-word;"></span>
                                <span id="indicator-pulse-help" style="display:none; color:#ef4444; font-size:0.92rem; margin-top:2px;"></span>
                            </div>
                            <div id="indicator-spo2" style="display:flex; flex-direction:column; align-items:center;">
                                <span class="bi bi-droplet-half" id="indicator-spo2-icon" style="font-size:2rem; color:#888;"></span>
                                <span style="font-size:0.95rem;">SpO₂</span>
                                <span id="indicator-spo2-status">
                                    <span class="sensor-loader"></span>
                                </span>
                                <span id="indicator-spo2-value" style="font-size:1.05rem; color:#333; margin-top:2px;"></span>
                                <span id="indicator-spo2-text" style="font-size:0.92rem; color:#666; text-align:center; word-break:break-word;"></span>
                                <span id="indicator-spo2-help" style="display:none; color:#ef4444; font-size:0.92rem; margin-top:2px;"></span>
                            </div>
                            <div id="indicator-bp" style="display:flex; flex-direction:column; align-items:center;">
                                <span class="bi bi-activity" id="indicator-bp-icon" style="font-size:2rem; color:#888;"></span>
                                <span style="font-size:0.95rem;">BP</span>
                                <span id="indicator-bp-status">
                                    <span class="sensor-loader"></span>
                                </span>
                                <span id="indicator-bp-value" style="font-size:1.05rem; color:#333; margin-top:2px;"></span>
                                <span id="indicator-bp-text" style="font-size:0.92rem; color:#666; text-align:center; word-break:break-word;"></span>
                                <span id="indicator-bp-help" style="display:none; color:#ef4444; font-size:0.92rem; margin-top:2px;"></span>
                            </div>
                        </div>
                        <div style="margin-top:18px;">
                            <span style="color:#888; font-size:1rem;">Please wait while we validate your readings...</span>
                        </div>
                        <!-- Proceed Button (hidden by default) -->
                        <div id="diagnosisProceedContainer" style="width:100%;display:none;justify-content:center;margin-top:24px;">
                            <button id="diagnosisProceedBtn" type="button" class="bg-green-500 text-white px-8 py-2 rounded-md hover:bg-green-700 text-lg font-semibold">Proceed</button>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById("diagnosisButton").addEventListener("click", function (event) {
                        const uid = document.getElementById("uid").innerText.trim();
                        const temp = document.getElementById("temp").innerText.trim();
                        const ecg = document.getElementById("ecg").innerText.trim();
                        const pulseRate = document.getElementById("pulse_rate").innerText.trim();
                        const spo2 = document.getElementById("spo2").innerText.trim();
                        const bp = document.getElementById("bp").innerText.trim();

                        if (!uid || uid === "N/A") {
                            event.preventDefault();
                            var modal = document.getElementById("diagnosisModal");
                            if (modal) modal.style.display = "none";
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
                        event.preventDefault();
                        var validMap = {
                            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
                            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
                            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
                            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
                            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
                        };
                        showDiagnosisModal(validMap, function() {
                            document.getElementById("sendToMailForm").submit();
                        });
                    });

                    // Consult button (redirect to consult.php with sensor summary)
                    document.getElementById("consultButton").addEventListener("click", function (event) {
                        const uid = document.getElementById("uid").innerText.trim();
                        const temp = document.getElementById("temp").innerText.trim();
                        const ecg = document.getElementById("ecg").innerText.trim();
                        const pulseRate = document.getElementById("pulse_rate").innerText.trim();
                        const spo2 = document.getElementById("spo2").innerText.trim();
                        const bp = document.getElementById("bp").innerText.trim();

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
                        event.preventDefault();
                        var validMap = {
                            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
                            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
                            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
                            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
                            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
                        };
                        showDiagnosisModal(validMap, function() {
                            // Build sensor summary and redirect to consult.php with it as a GET param
                            const summary = encodeURIComponent(buildSensorSummary());
                            window.location.href = "consult.php?sensor_summary=" + summary;
                        });
                    });

                    // Close button logic
                    document.getElementById("diagnosisModalClose").onclick = function() {
                        document.getElementById("diagnosisModal").style.display = "none";
                    };
                </script>
            </div>

            <!-- User Details Panel -->
            <div class="user-panel w-1/3">
                <h3>User Details</h3>
                <table>
                    <tr>
                        <td>UID:</td>
                        <td id="uid">Loading...</td>
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
                    <a href="my_results.php" id="myResultsLink" class="bg-green-500 text-white px-16 py-12 rounded-lg text-4xl font-bold hover:bg-green-800 block w-full h-40 flex items-center justify-center">TAP ID</a>
                    <div class="flex justify-center mt-2 text-green-600 text-5xl" id="myResultsIcon">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                </div>
            </div>
        </div>
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

    <script>
        async function fetchSensorData() {
            try {
                const response = await fetch("fetch_random.php"); // Change to fetch_data.php for ESP data change to .sample
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
                const myResultsLink = document.getElementById("myResultsLink");
                const myResultsIcon = document.getElementById("myResultsIcon");
                if (uid) {
                    myResultsButton.style.display = "block";
                    myResultsLink.textContent = "My Results";
                    myResultsLink.classList.remove("pointer-events-none", "opacity-60");
                    myResultsLink.classList.add("opacity-100");
                    myResultsLink.href = "my_results.php";
                    myResultsLink.setAttribute("tabindex", "0");
                    // Change icon to checklist
                    myResultsIcon.innerHTML = '<i class="bi bi-card-checklist"></i>';
                } else {
                    myResultsButton.style.display = "block";
                    myResultsLink.textContent = "TAP ID";
                    myResultsLink.classList.add("pointer-events-none", "opacity-60");
                    myResultsLink.classList.remove("opacity-100");
                    myResultsLink.href = "#";
                    myResultsLink.setAttribute("tabindex", "-1");
                    // Change icon to broadcast
                    myResultsIcon.innerHTML = '<i class="bi bi-broadcast"></i>';
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
                } else {
                    // Set all fields to N/A if no UID
                    document.getElementById("name").innerText = "N/A";
                    document.getElementById("email").innerText = "N/A";
                    document.getElementById("age").innerText = "N/A";
                    document.getElementById("weight").innerText = "N/A";
                    document.getElementById("height").innerText = "N/A";
                    document.getElementById("gender").innerText = "N/A";
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