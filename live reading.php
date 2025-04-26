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
    </style>
    <script src="jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            let lastUID = ""; // Store the last fetched UID to avoid unnecessary updates
            let inactivityTimer;

            function resetInactivityTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(() => {
                    location.reload(); // Refresh the page after 12 seconds of inactivity
                }, 12000); // 12 seconds
            }

            // Reset timer on any user interaction
            $(document).on('mousemove keydown click scroll touchstart', resetInactivityTimer);

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
                        resetInactivityTimer();
                    })
                    .fail(function() {
                        clearUserDetails(true);
                        showNoMatchAlert();
                        resetInactivityTimer();
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
            resetInactivityTimer(); // Start inactivity timer on load
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById("diagnosisButton").addEventListener("click", function (event) {
            const uid = document.getElementById("uid").innerText.trim();
            if (!uid || uid === "N/A") {
                event.preventDefault(); // Prevent form submission
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
                        <p class="label">Body Temperature</p>
                        <p class="value" id="temp">0.00 °C</p>
                    </div>
                    <div class="grid-item yellow">
                        <p class="label">ECG</p>
                        <p class="value" id="ecg">0.00</p>
                    </div>
                    <div class="grid-item green">
                        <p class="label">Pulse Rate</p>
                        <p class="value" id="pulse_rate">0 BPM</p>
                    </div>
                    <div class="grid-item blue">
                        <p class="label">SpO₂</p>
                        <p class="value" id="spo2">0.00 %</p>
                    </div>
                    <div class="grid-item purple col-span-2">
                        <p class="label">Blood Pressure</p>
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
                </div>
                <script>
                    document.getElementById("diagnosisButton").addEventListener("click", function (event) {
                        const uid = document.getElementById("uid").innerText.trim();
                        if (!uid || uid === "N/A") {
                            event.preventDefault(); // Prevent form submission
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
                    <a href="my_results.php" class="bg-green-500 text-white px-16 py-12 rounded-lg text-4xl font-bold hover:bg-green-800 block w-full h-40 flex items-center justify-center">My Results</a>
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