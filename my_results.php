<?php
include 'database.php'; 
$pdo = Database::connect();

// Fetch UID from UIDContainer.php
$uid = trim(file_get_contents('UIDContainer.php'));

// Fetch all rows where id matches the UID
$stmt = $pdo->prepare("SELECT count, patient_name, temperature, ecg_rate, pulse_rate, spo2_level, blood_pressure, diagnosis, created_at FROM health_readings WHERE id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extract the patient name from the first row (if available)
$patientName = !empty($readings) && !empty($readings[0]['patient_name']) ? $readings[0]['patient_name'] : 'Unknown Patient';

Database::disconnect();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="pageTitle"><?php echo htmlspecialchars($patientName); ?>'s Results</title>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: url('microcity.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .diagnosis-cell .full-text { display: none; }
        .diagnosis-cell.expanded .full-text { display: block; white-space: pre-line; margin-top: 0.5rem; }
        .diagnosis-cell.expanded .short-text { display: none; }
        .diagnosis-cell .expand-btn {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 12px;
            margin-left: 5px;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .diagnosis-cell.expanded {
            background: #f0fdf4;
            border-radius: 0.5rem;
            padding: 0.5rem;
            font-size: 1.05rem;
            color: #065f46;
        }
        .highlighted { background-color: #fef08a !important; }
        table, th, td {
            border: 1px solid #d1fae5 !important;
        }
        th, td {
            border: 1px solid #d1fae5 !important;
        }
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
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.8);
        }
    </style>
    <script>
        let lastUID = null;
        let inactivityTimeout;

        function resetInactivityTimer() {
            clearTimeout(inactivityTimeout);
            inactivityTimeout = setTimeout(() => {
                window.location.href = 'index.php';
            }, 20000);
        }

        async function fetchUIDAndResults() {
            try {
                const uidResponse = await fetch("UIDContainer.php", { cache: "no-store" });
                if (!uidResponse.ok) throw new Error(`Failed to fetch UID: ${uidResponse.statusText}`);
                const uid = (await uidResponse.text()).trim();

                if (uid && uid !== lastUID) {
                    lastUID = uid;
                    document.getElementById("uid").innerText = uid;
                    const resultsResponse = await fetch(`fetch_results.php?uid=${encodeURIComponent(uid)}`);
                    if (!resultsResponse.ok) throw new Error(`Failed to fetch results: ${resultsResponse.statusText}`);
                    const results = await resultsResponse.json();
                    updateResultsTable(results);
                    updateTitleAndHeading(results);
                }
            } catch (error) {
                console.error("Error fetching UID or results:", error);
            }
        }

        function updateResultsTable(results) {
            const tableBody = document.querySelector("#resultsTable tbody");
            const dropdown = document.getElementById("rowSelector");
            tableBody.innerHTML = "";
            dropdown.innerHTML = '<option value="all">All Results</option><option value="select">Select Result</option>';
            if (results.length > 0) {
                results.forEach((reading, index) => {
                    const row = document.createElement("tr");
                    row.id = `row-${reading.count}`;
                    const truncatedDiagnosis = reading.diagnosis.length > 50
                        ? reading.diagnosis.substring(0, 50) + "..."
                        : reading.diagnosis;
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${reading.temperature}</td>
                        <td>${reading.ecg_rate}</td>
                        <td>${reading.pulse_rate}</td>
                        <td>${reading.spo2_level}</td>
                        <td>${reading.blood_pressure}</td>
                        <td class="diagnosis-cell">
                            <span class="short-text">${truncatedDiagnosis}</span>
                            <span class="full-text">${reading.diagnosis}</span>
                            ${reading.diagnosis.length > 50 ? '<span class="expand-btn" onclick="toggleDiagnosis(this)">Read More</span>' : ''}
                        </td>
                        <td>${reading.created_at}</td>
                        <td>
                            <button onclick="deleteRow(${reading.count})" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                    const option = document.createElement("option");
                    option.value = index;
                    option.textContent = `Result #${index + 1}`;
                    dropdown.appendChild(option);
                });
            } else {
                const row = document.createElement("tr");
                row.innerHTML = `<td colspan="9">No health readings found for this UID.</td>`;
                tableBody.appendChild(row);
            }
        }

        function updateTitleAndHeading(results) {
            if (results.length > 0) {
                const patientName = results[0].patient_name;
                document.getElementById("pageTitle").innerText = `${patientName}'s Results`;
                document.getElementById("pageHeading").innerText = `${patientName}'s Results`;
            } else {
                document.getElementById("pageTitle").innerText = "Unknown Patient's Results";
                document.getElementById("pageHeading").innerText = "Unknown Patient's Results";
            }
        }

        function toggleDiagnosis(button) {
            const cell = button.closest(".diagnosis-cell");
            const row = cell.closest("tr");
            const tableBody = row.parentElement;
            const allRows = Array.from(tableBody.children);

            if (!cell.classList.contains("expanded")) {
                // Hide all other rows except the current one
                allRows.forEach(r => {
                    if (r !== row) r.style.display = "none";
                });
                cell.classList.add("expanded");
                button.textContent = "Read Less";
                setTimeout(() => {
                    cell.scrollIntoView({ behavior: "smooth", block: "center" });
                }, 100);
            } else {
                // Show all rows again
                allRows.forEach(r => r.style.display = "table-row");
                cell.classList.remove("expanded");
                button.textContent = "Read More";
            }
        }

        function handleDropdownChange() {
            const dropdown = document.getElementById("rowSelector");
            const inputBox = document.getElementById("multiSelectInput");
            if (dropdown.value === "select") {
                dropdown.style.display = "none";
                inputBox.style.display = "inline-block";
                inputBox.focus();
            } else {
                inputBox.style.display = "none";
                displayResults(dropdown.value);
            }
        }

        function handleInputBlur() {
            const dropdown = document.getElementById("rowSelector");
            const inputBox = document.getElementById("multiSelectInput");
            inputBox.style.display = "none";
            dropdown.style.display = "inline-block";
            dropdown.value = "all";
            displayResults("all");
        }

        function handleInputChange() {
            const inputBox = document.getElementById("multiSelectInput");
            displayResults("select", inputBox.value);
        }

        function parseInput(input) {
            const selectedRows = new Set();
            const parts = input.split(',');
            parts.forEach(part => {
                if (part.includes('-')) {
                    const [start, end] = part.split('-').map(num => parseInt(num.trim(), 10));
                    if (!isNaN(start) && !isNaN(end) && start <= end) {
                        for (let i = start; i <= end; i++) {
                            selectedRows.add(i - 1);
                        }
                    }
                } else {
                    const num = parseInt(part.trim(), 10);
                    if (!isNaN(num)) {
                        selectedRows.add(num - 1);
                    }
                }
            });
            return Array.from(selectedRows).sort((a, b) => a - b);
        }

        function displayResults(selection, inputValue = "") {
            const tableBody = document.querySelector("#resultsTable tbody");
            const allRows = Array.from(tableBody.children);
            allRows.forEach(row => row.classList.remove("highlighted"));
            if (selection === "all") {
                allRows.forEach(row => { row.style.display = "table-row"; });
            } else if (selection === "select") {
                const selectedRows = parseInput(inputValue);
                allRows.forEach((row, index) => {
                    if (selectedRows.includes(index)) {
                        row.style.display = "table-row";
                        row.classList.add("highlighted");
                    } else {
                        row.style.display = "none";
                    }
                });
            } else {
                allRows.forEach((row, index) => {
                    row.style.display = index === parseInt(selection) ? "table-row" : "none";
                });
            }
        }

        async function deleteRow(count) {
            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            });
            if (confirmation.isConfirmed) {
                try {
                    const response = await fetch(`delete_row.php`, {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `count=${count}`
                    });
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const result = await response.json();
                    if (result.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'The row has been deleted.' });
                        const row = document.getElementById(`row-${count}`);
                        if (row) { row.remove(); }
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete the row.' });
                    }
                } catch (error) {
                    console.error("Error deleting row:", error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while trying to delete the row.' });
                }
            }
        }

        function printSelectedRow() {
            const dropdown = document.getElementById("rowSelector");
            const inputBox = document.getElementById("multiSelectInput");
            const selectedValue = dropdown.value;
            const tableBody = document.querySelector("#resultsTable tbody");
            const allRows = Array.from(tableBody.children);
            let contentToPrint = "";

            // Helper to wrap each result in a page-break div
            function wrapResult(content) {
                return `<div style="page-break-after: always;">${content}</div>`;
            }

            if (selectedValue === "all") {
                allRows.forEach((row, index) => {
                    const diagnosisCell = row.querySelector(".diagnosis-cell .full-text") || row.querySelector(".diagnosis-cell .short-text");
                    const diagnosis = diagnosisCell ? diagnosisCell.textContent : "No diagnosis available";
                    const createdAt = row.children[7].textContent;
                    const uid = document.getElementById("uid").textContent;
                    const resultContent = `
                        <h1 style="text-align:center;">Diagnosis Result</h1>
                        <div style="text-align: justify;">
                            <p><strong>Result #${index + 1}</strong></p>
                            <p><strong>UID:</strong> ${uid}</p>
                            <p><strong>Date:</strong> ${createdAt}</p>
                            <hr>
                            <p>${diagnosis}</p>
                        </div>
                    `;
                    contentToPrint += wrapResult(resultContent);
                });
            } else if (selectedValue === "select") {
                const selectedRows = parseInput(inputBox.value);
                selectedRows.forEach(index => {
                    if (index >= 0 && index < allRows.length) {
                        const row = allRows[index];
                        const diagnosisCell = row.querySelector(".diagnosis-cell .full-text") || row.querySelector(".diagnosis-cell .short-text");
                        const diagnosis = diagnosisCell ? diagnosisCell.textContent : "No diagnosis available";
                        const createdAt = row.children[7].textContent;
                        const uid = document.getElementById("uid").textContent;
                        const resultContent = `
                            <h1 style="text-align:center;">Diagnosis Result</h1>
                            <div style="text-align: justify;">
                                <p><strong>Result #${index + 1}</strong></p>
                                <p><strong>UID:</strong> ${uid}</p>
                                <p><strong>Date:</strong> ${createdAt}</p>
                                <hr>
                                <p>${diagnosis}</p>
                            </div>
                        `;
                        contentToPrint += wrapResult(resultContent);
                    }
                });
            } else {
                const selectedRow = allRows[selectedValue];
                const diagnosisCell = selectedRow.querySelector(".diagnosis-cell .full-text") || selectedRow.querySelector(".diagnosis-cell .short-text");
                const diagnosis = diagnosisCell ? diagnosisCell.textContent : "No diagnosis available";
                const createdAt = selectedRow.children[7].textContent;
                const uid = document.getElementById("uid").textContent;
                contentToPrint = wrapResult(`
                    <h1 style="text-align:center;">Diagnosis Result</h1>
                    <div style="text-align: justify;">
                        <p><strong>UID:</strong> ${uid}</p>
                        <p><strong>Date:</strong> ${createdAt}</p>
                        <hr>
                        <p>${diagnosis}</p>
                    </div>
                `);
            }

            const printWindow = window.open("", "_blank");
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Diagnosis</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                            h1 { text-align: center; margin-bottom: 20px; }
                            p { margin: 10px 0; }
                            hr { margin: 20px 0; border: none; border-top: 1px solid #ddd; }
                            div { margin-bottom: 30px; text-align: justify; }
                            .page-break { page-break-after: always; }
                            footer { text-align: center; margin-top: 30px; font-size: 14px; color: #555; }
                        </style>
                    </head>
                    <body>
                        ${contentToPrint}
                        <footer>AI-Vital</footer>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            if (selectedValue === "select") {
                const selectedRows = parseInput(inputBox.value);
                allRows.forEach((row, index) => {
                    if (selectedRows.includes(index)) {
                        row.classList.add("highlighted");
                    }
                });
            }
        }

        function clearUserDetailsAndRedirect() {
            fetch('UIDContainer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'clear=true'
            }).then(() => {
                window.location.href = 'live reading.php';
            }).catch(error => {
                console.error('Error clearing user details:', error);
            });
        }

        setInterval(fetchUIDAndResults, 2000);

        document.addEventListener("DOMContentLoaded", () => {
            resetInactivityTimer();
            ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, resetInactivityTimer);
            });
            const inputBox = document.getElementById("multiSelectInput");
            const printButton = document.querySelector(".print-controls button");
            inputBox.addEventListener("keypress", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                    printSelectedRow();
                }
            });
            printButton.addEventListener("mousedown", (event) => {
                // Prevent form submission or focus loss before click
                event.preventDefault();
            });
            printButton.addEventListener("click", (event) => {
                event.preventDefault();
                // If the input box is visible (i.e., select mode), always update highlights before printing
                if (inputBox.style.display !== "none") {
                    displayResults("select", inputBox.value);
                }
                printSelectedRow();
            });
        });
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
                        <li><a href="registration2.php">Registration</a></li>
                        <li><a href="userdata2.php">User Data</a></li>
                        <li><a href="live reading.php">Live-Reading</a></li>
                        <li><a href="results2.php">Results</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <h2 id="pageHeading" class="text-center text-2xl font-bold mb-4"><?php echo htmlspecialchars($patientName); ?>'s Results</h2>
            <p class="text-center text-lg font-semibold mb-4">UID: <span id="uid" class="font-mono"><?php echo htmlspecialchars($uid); ?></span></p>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="overflow-x-auto">
                    <table id="resultsTable" class="min-w-full divide-y divide-gray-200 border border-green-200 bg-white">
                        <thead class="bg-green-500 text-white">
                            <tr>
                                <th class="px-3 py-2 text-center">#</th>
                                <th class="px-3 py-2 text-center">Temperature (°C)</th>
                                <th class="px-3 py-2 text-center">ECG Rate</th>
                                <th class="px-3 py-2 text-center">Pulse Rate (BPM)</th>
                                <th class="px-3 py-2 text-center">SpO₂ (%)</th>
                                <th class="px-3 py-2 text-center">Blood Pressure</th>
                                <th class="px-3 py-2 text-center">Diagnosis</th>
                                <th class="px-3 py-2 text-center">Created At</th>
                                <th class="px-3 py-2 text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($readings)): ?>
                            <?php foreach ($readings as $index => $reading): ?>
                                <tr id="row-<?php echo $reading['count']; ?>" class="hover:bg-green-50<?php if($index === 0) echo ' text-center'; ?>">
                                    <td class="px-3 py-2 text-center"><?php echo $index + 1; ?></td>
                                    <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($reading['temperature']); ?></td>
                                    <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($reading['ecg_rate']); ?></td>
                                    <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($reading['pulse_rate']); ?></td>
                                    <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($reading['spo2_level']); ?></td>
                                    <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($reading['blood_pressure']); ?></td>
                                    <td class="diagnosis-cell px-3 py-2 text-center">
                                        <span class="short-text"><?php echo htmlspecialchars(substr($reading['diagnosis'], 0, 50)); ?>...</span>
                                        <span class="full-text"><?php echo htmlspecialchars($reading['diagnosis']); ?></span>
                                        <?php if (strlen($reading['diagnosis']) > 50): ?>
                                            <span class="expand-btn" onclick="toggleDiagnosis(this)">Read More</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($reading['created_at']); ?></td>
                                    <td class="px-3 py-2 text-center">
                                        <button onclick="deleteRow(<?php echo $reading['count']; ?>)" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">No health readings found for this UID.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="print-controls flex flex-col md:flex-row justify-center items-center gap-4 mt-6">
                    <select id="rowSelector" onchange="handleDropdownChange()" class="rounded border-gray-300 px-3 py-2">
                        <option value="all">All Results</option>
                        <option value="select">Select Result</option>
                        <?php foreach ($readings as $index => $reading): ?>
                            <option value="<?php echo $index; ?>">Result #<?php echo $index + 1; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input id="multiSelectInput" type="text" placeholder="Enter result numbers (e.g., 1,2)" style="display: none;" onblur="handleInputBlur()" oninput="handleInputChange()" class="rounded border-gray-300 px-3 py-2" />
                    <button onclick="printSelectedRow()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Print</button>
                    <button onclick="clearUserDetailsAndRedirect()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Log Out</button>
                </div>
            </div>
        </div>
        <!-- Footer Section -->
        <footer id="footer" class="footer dark-background mt-8">
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
                            Designed by Einsbern
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
    </div>
</body>
</html>
