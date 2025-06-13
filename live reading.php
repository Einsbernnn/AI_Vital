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
            background: #f0fdf4;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 2px solid #d1fae5;
            transition: all 0.3s ease;
        }
        .grid-item p {
            margin: 0;
        }
        .grid-item .label {
            font-size: 24px;
            font-weight: bold;
            color: #065f46; /* Dark green text */
            position: relative;
        }
        .grid-item .label i.vital-icon {
            position: relative;
            z-index: 1;
        }
        .grid-item .value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 6px;
        }
        .grid-item.red {
            border-color: #EF4444;
            animation: borderPulse 2s infinite;
        }
        .grid-item.yellow {
            border-color: #F59E0B;
            animation: borderPulseYellow 2s infinite;
        }
        .grid-item.green {
            border-color: #10B981;
            animation: borderPulseGreen 2s infinite;
        }
        .grid-item.blue {
            border-color: #3B82F6;
            animation: borderPulseBlue 2s infinite;
        }
        .grid-item.purple {
            border-color: #8B5CF6;
            animation: borderPulsePurple 2s infinite;
        }
        .grid-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        /* Add a subtle gradient background to each grid item */
        .grid-item.red { background: linear-gradient(145deg, #fff5f5, #f0fdf4); }
        .grid-item.yellow { background: linear-gradient(145deg, #fffbeb, #f0fdf4); }
        .grid-item.green { background: linear-gradient(145deg, #ecfdf5, #f0fdf4); }
        .grid-item.blue { background: linear-gradient(145deg, #eff6ff, #f0fdf4); }
        .grid-item.purple { background: linear-gradient(145deg, #f5f3ff, #f0fdf4); }
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
        #ecgCanvas {
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .grid-item.yellow {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        @keyframes iconPulse {
            0% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.1); filter: brightness(1.2); }
            100% { transform: scale(1); filter: brightness(1); }
        }

        @keyframes iconColorShift {
            0% { color: inherit; }
            25% { color: #22c55e; }
            50% { color: inherit; }
            75% { color: #22c55e; }
            100% { color: inherit; }
        }

        .vital-icon {
            font-size: 1.2em;
            margin-right: 4px;
            animation: iconPulse 2s ease-in-out infinite, iconColorShift 3s ease-in-out infinite;
        }

        .vital-icon.temp { color: #EF4444; }
        .vital-icon.ecg { color: #F59E0B; }
        .vital-icon.pulse { color: #10B981; }
        .vital-icon.spo2 { color: #3B82F6; }
        .vital-icon.bp { color: #8B5CF6; }

        .vital-icon:hover {
            animation-play-state: paused;
        }

        /* Add glow effect on hover */
        .vital-icon:hover {
            filter: drop-shadow(0 0 8px currentColor);
        }

        /* Add these styles before the existing styles */
        @keyframes borderPulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        @keyframes borderPulseYellow {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        @keyframes borderPulseGreen {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes borderPulseBlue {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        @keyframes borderPulsePurple {
            0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
        }

        /* Add these styles to your existing styles */
        #myResultsButton {
            margin-top: 1.5rem;
            padding: 0 1rem;
        }

        #myResultsLink {
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 8rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #myResultsLink span {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            white-space: nowrap;
        }

        #myResultsLink:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        #myResultsLink:active {
            transform: scale(0.98);
        }

        #myResultsIcon {
            margin-top: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #myResultsLink {
                min-height: 6rem;
                font-size: 1.5rem;
            }
            
            #myResultsLink span {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            #myResultsLink {
                min-height: 5rem;
                font-size: 1.25rem;
            }
            
            #myResultsLink span {
                font-size: 1.25rem;
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
    function buildSensorSummary(values) {
        // Accepts an object with keys: temp, ecg, pulse, spo2, bp
        return `Temp\n${values.temp}\nECG\n${values.ecg}\nPulse\n${values.pulse}\nSpO₂\n${values.spo2}\nBP\n${values.bp}`;
    }

    // Store frozen sensor values for diagnosis/consult
    let frozenSensorValues = null;

    // Reusable diagnosis modal logic
    function showDiagnosisModal(validMap, onSuccess, sensorValues) {
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
        // Use the provided sensorValues (frozen at modal open)
        const values = sensorValues;
        let anyInvalid = false;
        let invalidSensors = [];
        sensors.forEach(s => {
            document.getElementById(`indicator-${s}-status`).innerHTML = validMap[s] ? "✔️" : "❌";
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
                setTimeout(() => {
                    const key = keys[idx];
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
                    if (waitMsg) waitMsg.textContent = anyInvalid ? "Some readings are invalid." : "All readings are valid.";
                    // Show proceed button
                    document.getElementById("diagnosisProceedContainer").style.display = "flex";
                    document.getElementById("diagnosisProceedBtn").onclick = function() {
                        modal.style.display = "none";
                        if (typeof onSuccess === 'function') onSuccess();
                    };
                }, 600);
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
        // Freeze sensor values at modal open
        frozenSensorValues = {
            temp: temp,
            ecg: ecg,
            pulse: pulseRate,
            spo2: spo2,
            bp: bp
        };
        var validMap = {
            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
        };
        showDiagnosisModal(validMap, function() {
            // Set hidden form fields using frozen values
            document.getElementById("emailBodyTemp").value = frozenSensorValues.temp.replace(" °C", "").replace("N/A", "0.00");
            document.getElementById("emailEcg").value = frozenSensorValues.ecg.replace("N/A", "0.00");
            document.getElementById("emailPulseRate").value = frozenSensorValues.pulse.replace(" BPM", "").replace("N/A", "0");
            document.getElementById("emailSpo2").value = frozenSensorValues.spo2.replace(" %", "").replace("N/A", "0.00");
            document.getElementById("emailBp").value = frozenSensorValues.bp.replace("N/A", "0/0");
            document.getElementById("sendToMailForm").submit();
        }, frozenSensorValues);
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
        // Freeze sensor values at modal open
        frozenSensorValues = {
            temp: temp,
            ecg: ecg,
            pulse: pulseRate,
            spo2: spo2,
            bp: bp
        };
        var validMap = {
            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
        };
        showDiagnosisModal(validMap, function() {
            // Always build the summary from the frozen values after modal validation
            const summary = encodeURIComponent(buildSensorSummary(frozenSensorValues));
            window.location.href = "consult.php?sensor_summary=" + summary;
        }, frozenSensorValues);
    });

    // Close button logic
    document.getElementById("diagnosisModalClose").onclick = function() {
        document.getElementById("diagnosisModal").style.display = "none";
    };
    </script>
    <script src="bp_handler.js"></script>
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
                            <i class="bi bi-thermometer-half vital-icon temp"></i>
                            Body Temperature
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;touch-action:manipulation;"
                                onclick="toggleInfo('bodyTempInfo', event)"
                                onTouchStart="toggleInfo('bodyTempInfo', event)"
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
                            <i class="bi bi-activity vital-icon ecg"></i>
                            ECG
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;touch-action:manipulation;"
                                onclick="toggleInfo('ecgInfo', event)"
                                onTouchStart="toggleInfo('ecgInfo', event)"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </p>
                        <div id="ecgInfo" class="sensor-tooltip" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1fae5;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:0.98rem;max-width:220px;">
                            Normal: 60–100 BPM, regular rhythm<br>ECG measures heart electrical activity. Normal resting heart activity is steady and consistent.
                        </div>
                        <p class="value" id="ecg">0.00</p>
                        <canvas id="ecgCanvas" width="200" height="60" style="width: 100%; height: 60px; margin-top: 10px; background: #f8fafc;"></canvas>
                    </div>
                    <div class="grid-item green">
                        <p class="label" style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            <i class="bi bi-heart-pulse vital-icon pulse"></i>
                            Pulse Rate
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;touch-action:manipulation;"
                                onclick="toggleInfo('pulseInfo', event)"
                                onTouchStart="toggleInfo('pulseInfo', event)"
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
                            <i class="bi bi-droplet-half vital-icon spo2"></i>
                            SpO₂
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;touch-action:manipulation;"
                                onclick="toggleInfo('spo2Info', event)"
                                onTouchStart="toggleInfo('spo2Info', event)"
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
                            <i class="bi bi-heart vital-icon bp"></i>
                            Blood Pressure
                            <span 
                                tabindex="0"
                                style="color:#888;outline:none;cursor:pointer;touch-action:manipulation;"
                                onclick="toggleInfo('bpInfo', event)"
                                onTouchStart="toggleInfo('bpInfo', event)"
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
                        // Freeze sensor values at modal open
                        frozenSensorValues = {
                            temp: temp,
                            ecg: ecg,
                            pulse: pulseRate,
                            spo2: spo2,
                            bp: bp
                        };
                        var validMap = {
                            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
                            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
                            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
                            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
                            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
                        };
                        showDiagnosisModal(validMap, function() {
                            // Set hidden form fields using frozen values
                            document.getElementById("emailBodyTemp").value = frozenSensorValues.temp.replace(" °C", "").replace("N/A", "0.00");
                            document.getElementById("emailEcg").value = frozenSensorValues.ecg.replace("N/A", "0.00");
                            document.getElementById("emailPulseRate").value = frozenSensorValues.pulse.replace(" BPM", "").replace("N/A", "0");
                            document.getElementById("emailSpo2").value = frozenSensorValues.spo2.replace(" %", "").replace("N/A", "0.00");
                            document.getElementById("emailBp").value = frozenSensorValues.bp.replace("N/A", "0/0");
                            document.getElementById("sendToMailForm").submit();
                        }, frozenSensorValues);
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
                        // Freeze sensor values at modal open
                        frozenSensorValues = {
                            temp: temp,
                            ecg: ecg,
                            pulse: pulseRate,
                            spo2: spo2,
                            bp: bp
                        };
                        var validMap = {
                            temp: !!(temp && !/^0(\.00)? ?°C$/.test(temp) && !/^N\/A ?°C$/.test(temp)),
                            ecg: !!(ecg && !/^0(\.00)?$/.test(ecg) && !/^N\/A$/.test(ecg)),
                            pulse: !!(pulseRate && !/^0 ?BPM$/.test(pulseRate) && !/^N\/A ?BPM$/.test(pulseRate)),
                            spo2: !!(spo2 && !/^0(\.00)? ?%$/.test(spo2) && !/^N\/A ?%$/.test(spo2)),
                            bp: !!(bp && !/^N\/A ?mmHg$/.test(bp) && !/^0(\/0)?( mmHg)?$/.test(bp))
                        };
                        showDiagnosisModal(validMap, function() {
                            // Always build the summary from the frozen values after modal validation
                            const summary = encodeURIComponent(buildSensorSummary(frozenSensorValues));
                            window.location.href = "consult.php?sensor_summary=" + summary;
                        }, frozenSensorValues);
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
                    <a href="my_results.php" id="myResultsLink" class="bg-green-500 text-white px-4 py-3 rounded-lg text-2xl font-bold hover:bg-green-800 block w-full h-32 flex items-center justify-center transition-all duration-300 transform hover:scale-105">
                        <span class="flex items-center justify-center gap-3">
                            <i class="bi bi-card-checklist text-3xl"></i>
                            <span>My Results</span>
                        </span>
                    </a>
                    <div class="flex justify-center mt-2 text-green-600 text-3xl" id="myResultsIcon">
                        <i class="bi bi-broadcast"></i>
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

    <script>
        let ecgData = [];
        const maxDataPoints = 100;
        let animationFrameId = null;
        let lastECGValue = 0;

        // Initialize ECG functionality when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('ecgCanvas');
            if (!canvas) {
                console.error('ECG canvas not found in DOM');
                return;
            }
            console.log('ECG canvas found and initialized');
            
            // Start the ECG animation
            animateECG();
        });

        function initECGCanvas() {
            const canvas = document.getElementById('ecgCanvas');
            if (!canvas) {
                console.error('ECG canvas not found');
                return null;
            }
            const ctx = canvas.getContext('2d');
            
            // Set canvas size to match display size
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            
            return { canvas, ctx };
        }

        function drawECG(ctx, value) {
            if (!ctx) return;
            
            const width = ctx.canvas.width;
            const height = ctx.canvas.height;
            const centerY = height / 2;
            
            // Add new data point
            ecgData.push(value);
            if (ecgData.length > maxDataPoints) {
                ecgData.shift();
            }
            
            // Clear canvas
            ctx.clearRect(0, 0, width, height);
            
            // Draw grid lines
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 0.5;
            
            // Vertical grid lines
            for (let x = 0; x < width; x += 20) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, height);
                ctx.stroke();
            }
            
            // Horizontal grid lines
            for (let y = 0; y < height; y += 20) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(width, y);
                ctx.stroke();
            }
            
            // Draw ECG line
            ctx.strokeStyle = '#f59e0b';
            ctx.lineWidth = 2;
            ctx.beginPath();
            
            const pointWidth = width / (maxDataPoints - 1);
            
            ecgData.forEach((point, index) => {
                const x = index * pointWidth;
                // Scale the value to fit the canvas height
                const scaledValue = (point / 100) * (height * 0.4);
                const y = centerY - scaledValue;
                
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            
            ctx.stroke();
        }

        function animateECG() {
            const canvasContext = initECGCanvas();
            if (!canvasContext) return;
            
            const { ctx } = canvasContext;
            
            // Use the last ECG value from the sensor data
            const currentValue = lastECGValue;
            
            // Add some noise to make it look more realistic
            const noise = (Math.random() - 0.5) * 2;
            drawECG(ctx, currentValue + noise);
            
            animationFrameId = requestAnimationFrame(animateECG);
        }

        // Clean up animation when page is unloaded
        window.addEventListener('unload', () => {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
            }
        });

        // Original fetchSensorData function with ECG value tracking added
        async function fetchSensorData() {
            try {
                const response = await fetch("fetch_data.php"); // Keep original fetch_random.php
                const data = await response.json();

                // Debugging: Log the fetched data
                console.log("Fetched Sensor Data:", data);

                // Update the UI with fetched sensor data
                document.getElementById("temp").innerText = data.body_temp !== null ? parseFloat(data.body_temp).toFixed(2) + " °C" : "0.00 °C";
                
                // Update ECG value and store it for the graph
                const ecgValue = data.ecg !== null ? parseFloat(data.ecg) : 0;
                document.getElementById("ecg").innerText = ecgValue.toFixed(2);
                lastECGValue = ecgValue; // Store the ECG value for the graph
                
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

        // Make sure the canvas is properly initialized
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('ecgCanvas');
            if (!canvas) {
                console.error('ECG canvas not found in DOM');
            } else {
                console.log('ECG canvas found and initialized');
            }
        });

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

        // Add this function for handling info tooltips
        function toggleInfo(id, event) {
            event.preventDefault();
            event.stopPropagation();
            
            const tooltip = document.getElementById(id);
            const allTooltips = document.querySelectorAll('.sensor-tooltip');
            
            // Hide all other tooltips
            allTooltips.forEach(t => {
                if (t.id !== id) {
                    t.style.display = 'none';
                }
            });
            
            // Toggle current tooltip
            if (tooltip.style.display === 'none') {
                tooltip.style.display = 'block';
                
                // Position the tooltip
                const rect = event.target.getBoundingClientRect();
                tooltip.style.top = (rect.bottom + window.scrollY + 5) + 'px';
                tooltip.style.left = (rect.left + window.scrollX) + 'px';
                
                // Add click outside listener
                document.addEventListener('click', function closeTooltip(e) {
                    if (!tooltip.contains(e.target) && !e.target.closest('.bi-info-circle')) {
                        tooltip.style.display = 'none';
                        document.removeEventListener('click', closeTooltip);
                    }
                });
            } else {
                tooltip.style.display = 'none';
            }
        }
    </script>
</body>
</html>