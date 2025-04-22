<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['clerk1_logged_in']) && !isset($_SESSION['clerk2_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Clear UID on page load
$Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);

include 'database.php';
$pdo = Database::connect();

// Sorting and Filtering
$order = $_GET['order'] ?? 'id';
$sort = $_GET['sort'] ?? 'ASC';
$search = $_GET['search'] ?? ''; // Get search input
$date = $_GET['date'] ?? ''; // Get date input

$validColumns = ['id', 'name', 'gender', 'age', 'height', 'weight', 'body_temperature', 'blood_pressure', 'ecg', 'pulse_rate', 'spo2', 'timestamp'];
if (!in_array($order, $validColumns)) {
    $order = 'id';
}
$sort = ($sort === 'DESC') ? 'DESC' : 'ASC';

// Query with sorting, searching, and filtering by date
$sql = "SELECT id, name, gender, age, height, weight, body_temperature, blood_pressure, ecg, pulse_rate, spo2, timestamp 
        FROM health_data 
        WHERE (id LIKE ? OR name LIKE ? OR gender LIKE ? OR age LIKE ? OR height LIKE ? OR weight LIKE ? OR body_temperature LIKE ? OR blood_pressure LIKE ? OR ecg LIKE ? OR pulse_rate LIKE ? OR spo2 LIKE ? OR timestamp LIKE ?)
        AND (timestamp LIKE ?)
        ORDER BY $order $sort";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge(array_fill(0, 12, "%$search%"), ["%$date%"]));

$datesQuery = "SELECT DISTINCT DATE(timestamp) as date FROM health_data ORDER BY date DESC";
$datesStmt = $pdo->prepare($datesQuery);
$datesStmt->execute();
$dates = $datesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $selectedEntries = $_POST['selected_entries'] ?? [];
    
    if (!empty($selectedEntries)) {
        $deleteConditions = [];
        $params = [];

        foreach ($selectedEntries as $entry) {
            list($id, $timestamp) = explode('|', $entry);
            $deleteConditions[] = "(id = ? AND timestamp = ?)";
            $params[] = $id;
            $params[] = $timestamp;
        }

        $deleteSql = "DELETE FROM health_data WHERE " . implode(" OR ", $deleteConditions);
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute($params);

        header('Location: results2.php?search=' . urlencode($search) . '&date=' . urlencode($date)); // Preserve filters
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Vital: Results</title>

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
                        slideUp: 'slideUp 1s ease-in-out',
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
            plugins: [require('@tailwindcss/typography')],
        };
    </script>
    <script>
        // Clear UID and refresh details on page load
        document.addEventListener("DOMContentLoaded", function () {
            fetch("UIDContainer.php", { cache: "no-store" })
                .then(response => response.text())
                .then(uid => {
                    if (!uid.trim()) {
                        document.getElementById("uid").innerText = "N/A";
                        document.getElementById("name").innerText = "N/A";
                        document.getElementById("age").innerText = "N/A";
                        document.getElementById("weight").innerText = "N/A";
                        document.getElementById("height").innerText = "N/A";
                        document.getElementById("gender").innerText = "N/A";
                    }
                });
        });
    </script>
</head>
<body>
    <!-- Header Section -->
    <header id="header" class="header d-flex align-items-center position-relative">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.php" class="logo d-flex align-items-center">
                <img src="img/logo.png" alt="AI Vital">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="registration2.php">Registration</a></li>
                    <li><a href="userdata2.php">User Data</a></li>
                    <li><a href="live reading.php">Live-Reading</a></li>
                    <li><a href="results2.php" class="active">Results</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="bg-white min-h-screen">
            <main class="flex-grow container mx-auto px-4 py-8">
                <div class="bg-white shadow-lg rounded-lg p-6 text-center animate-fadeIn">
                    <h1 class="text-3xl font-bold text-green-700 mb-4">Vital Sign Results</h1>
                    <form method="GET" class="flex justify-between items-center mb-6">
                        <input type="text" name="search" placeholder="Search Here" value="<?= htmlspecialchars($search) ?>" class="border border-gray-300 rounded-md px-4 py-2 w-1/3">
                        <select name="date" class="border border-gray-300 rounded-md px-4 py-2">
                            <option value="">All Results</option>
                            <?php foreach ($dates as $dateRow) { ?>
                                <option value="<?= $dateRow['date'] ?>" <?= ($dateRow['date'] == $date) ? 'selected' : '' ?>><?= $dateRow['date'] ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded-md">Search</button>
                    </form>

                    <form method="POST" id="deleteForm">
                        <table class="table-auto w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-green-700 text-white">
                                    <th class="border border-gray-300 px-4 py-2">#</th>
                                    <th class="border border-gray-300 px-4 py-2">Select</th>
                                    <th class="border border-gray-300 px-4 py-2">ID</th>
                                    <th class="border border-gray-300 px-4 py-2">Name</th>
                                    <th class="border border-gray-300 px-4 py-2">Gender</th>
                                    <th class="border border-gray-300 px-4 py-2">Age</th>
                                    <th class="border border-gray-300 px-4 py-2">Height (cm)</th>
                                    <th class="border border-gray-300 px-4 py-2">Weight (kg)</th>
                                    <th class="border border-gray-300 px-4 py-2">Body Temperature (°C)</th>
                                    <th class="border border-gray-300 px-4 py-2">Blood Pressure</th>
                                    <th class="border border-gray-300 px-4 py-2">ECG</th>
                                    <th class="border border-gray-300 px-4 py-2">Pulse Rate (BPM)</th>
                                    <th class="border border-gray-300 px-4 py-2">SpO2 (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rowNumber = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2"><?= $rowNumber++ ?></td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <input type="checkbox" name="selected_entries[]" value="<?= htmlspecialchars($row['id']) ?>|<?= htmlspecialchars($row['timestamp']) ?>">
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['id']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['gender']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['age']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['height']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['weight']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['body_temperature'] ?? '0.00') ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['blood_pressure'] ?? 'N/A') ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['ecg'] ?? '0.00') ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['pulse_rate'] ?? '0.0') ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['spo2'] !== null ? number_format($row['spo2'], 2) : '0.00') ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded-md mt-4">Delete Selected</button>
                    </form>
                </div>
            </main>
        </div>
    </main>

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
</body>
</html>
