<?php
session_start();
$Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Vital: Registration</title>

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
        }
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.8);
        }
        /* Nav menu as links only, no button style, always visible and horizontal */
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
        /* No column direction on mobile, always horizontal */
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
        .input-group {
            display: flex;
            align-items: center;
        }
        .input-group span {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-right: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem 0 0 0.375rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .input-group input {
            border: 1px solid #d1d5db;
            border-radius: 0 0.375rem 0.375rem 0;
            padding: 0.5rem;
            flex: 1;
        }
        .error-message {
            color: #EF4444; /* Red color for errors */
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .success-message {
            color: #10B981; /* Green color for success */
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
    <script src="jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            function fetchUID() {
                $.get("UIDContainer.php", { cache: "no-store" }, function(data) {
                    const uid = data.trim();
                    console.log("Fetched UID:", uid);

                    // Update the form field and hidden input with the UID
                    $("#getUID").val(uid ? uid : ""); // Leave empty if no UID is detected
                    $("#hiddenUID").val(uid ? uid : "");

                    // Clear UID feedback if no UID is detected
                    const feedback = $("#uidFeedback");
                    if (!uid) {
                        feedback.text("").removeClass("error-message success-message");
                        return;
                    }

                    // Save UID to localStorage
                    if (uid) {
                        localStorage.setItem("lastUID", uid);
                    }

                    // Check if UID is already taken
                    checkUIDAvailability(uid);
                });
            }

            function checkUIDAvailability(uid) {
                $.post("check_uid.php", { uid: uid }, function(response) {
                    const feedback = $("#uidFeedback");
                    if (response.exists) {
                        feedback.text("This UID is already registered.").removeClass("success-message").addClass("error-message");
                    } else {
                        feedback.text("This UID is available.").removeClass("error-message").addClass("success-message");
                    }
                }, "json");
            }

            // Load UID from localStorage on page load
            const savedUID = localStorage.getItem("lastUID");
            if (savedUID) {
                $("#getUID").val(savedUID);
                $("#hiddenUID").val(savedUID);
                checkUIDAvailability(savedUID);
            }

            fetchUID(); // Fetch UID immediately on page load
            setInterval(fetchUID, 300); // Fetch UID every 300 milliseconds

            // Real-time validation
            $("#registrationForm input, #registrationForm select").on("input change", function() {
                validateField($(this));
            });

            function validateField(field) {
                const id = field.attr("id");
                const value = field.val();
                let errorMessage = "";

                if (id === "name" && value.length > 50) {
                    errorMessage = "Name must not exceed 50 characters.";
                } else if (id === "email" && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)) {
                    errorMessage = "Please enter a valid email address.";
                } else if (id === "mobile" && (!/^[0-9]{10}$/.test(value) || value.charAt(0) !== '9')) {
                    errorMessage = "Please use a valid mobile number.";
                } else if (id === "age" && (value < 1 || value > 120)) {
                    errorMessage = "Age must be between 1 and 120.";
                } else if (id === "height" && value <= 0) {
                    errorMessage = "Height must be a positive number.";
                } else if (id === "weight" && value <= 0) {
                    errorMessage = "Weight must be a positive number.";
                }

                const errorElement = field.siblings(".error-message");
                if (errorMessage) {
                    if (errorElement.length === 0) {
                        field.after(`<div class="error-message">${errorMessage}</div>`);
                    } else {
                        errorElement.text(errorMessage);
                    }
                } else {
                    errorElement.remove();
                }
            }
        });

        function clearForm() {
            document.getElementById("registrationForm").reset();
            $(".error-message").remove(); // Clear all error messages
            $(".success-message").remove(); // Clear all success messages
            $("#uidFeedback").text("").removeClass("error-message success-message"); // Clear UID feedback
        }
    </script>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <div class="bg-overlay min-h-screen">
        
    <!-- Header Section -->
    <header id="header" class="header d-flex align-items-center position-relative">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.php" class="logo d-flex align-items-center">
                <img src="img/logo.png" alt="AI Vital">
            </a>

            <nav id="navmenu">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="registration2.php" class="active">Registration</a></li>
                    <li><a href="userdata2.php">User Data</a></li>
                    <li><a href="live reading.php">Live-Reading</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </nav>

        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-center text-2xl font-bold mb-4">Register your RFID card to use "AI-Vital Diagnoser"</h2>
        <form id="registrationForm" action="insertDB2.php" method="post" class="bg-white p-6 rounded-lg shadow-md">
            <div class="form-group mb-4">
                <label for="id" class="block text-sm font-medium text-gray-700">ID</label>
                <textarea name="id" id="getUID" placeholder="Please Tap Your RFID Card to Fill Out This Area" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                <input type="hidden" name="hiddenUID" id="hiddenUID">
                <div id="uidFeedback" class="mt-1"></div>
            </div>
            <div class="form-group mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" maxlength="50" oninput="this.value = this.value.toUpperCase();" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="form-group mb-4">
                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                <select name="gender" id="gender" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="" disabled selected>Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="form-group mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter a valid email address" value="<?php echo $email; ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="form-group mb-4">
                <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                <div class="input-group">
                    <span>+63</span>
                    <input type="text" name="mobile" id="mobile" pattern="[0-9]{10}" maxlength="10" placeholder="9123456789" required>
                </div>
                <div class="error-message"></div>
            </div>
            <div class="form-group mb-4">
                <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                <input type="number" name="age" id="age" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="form-group mb-4">
                <label for="height" class="block text-sm font-medium text-gray-700">Height (cm)</label>
                <input type="number" name="height" id="height" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="form-group mb-4">
                <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                <input type="number" name="weight" id="weight" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="button-group flex justify-center gap-4">
                <button type="submit" class="btn-save bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Save</button>
                <button type="button" class="btn-clear bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600" onclick="clearForm()">Clear Form</button>
            </div>
        </form>
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
</body>
</html>
