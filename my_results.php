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
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #d4fc79, #96e6a1);
            text-align: center;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure the body takes up the full height of the viewport */
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            flex: 1; /* Allow the container to grow and push the footer to the bottom */
        }
        .uid-display {
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .diagnosis-cell {
            text-align: left;
            max-width: 300px;
            word-wrap: break-word;
            position: relative;
        }
        .diagnosis-cell .full-text {
            display: none;
        }
        .diagnosis-cell.expanded .full-text {
            display: inline;
        }
        .diagnosis-cell.expanded .short-text {
            display: none;
        }
        .diagnosis-cell .expand-btn {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 12px;
            margin-left: 5px;
        }
        .highlighted {
            background-color: #ffff99 !important;
        }
        .print-controls {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .print-controls select, .print-controls input {
            padding: 5px;
            font-size: 14px;
        }
        .button {
            font-size: 14px; /* Standard font size */
            padding: 10px 20px; /* Standard padding */
            border: none;
            border-radius: 5px; /* Rounded corners */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .button.print {
            background-color: #28a745; /* Green background */
            color: white; /* White text */
        }
        .button.print:hover {
            background-color: #218838; /* Darker green on hover */
        }
        .button.logout {
            background-color: #EF4444; /* Red background */
            color: white; /* White text */
        }
        .button.logout:hover {
            background-color: #DC2626; /* Darker red on hover */
        }
        .enlarged-heading {
            font-size: 36px; /* Larger font size for heading */
            font-weight: bold;
            margin-bottom: 20px;
        }
        .enlarged-uid {
            font-size: 24px; /* Larger font size for UID */
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }
        footer {
            background-color: #2E8B57;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: auto; /* Push the footer to the bottom */
        }
    </style>
    <script>
        let lastUID = null;
        let inactivityTimeout;

        function resetInactivityTimer() {
            clearTimeout(inactivityTimeout);
            inactivityTimeout = setTimeout(() => {
                window.location.href = 'index.php'; // Redirect to index.php after 20 seconds of inactivity
            }, 20000); // 20 seconds
        }

        async function fetchUIDAndResults() {
            try {
                // Fetch the latest UID
                const uidResponse = await fetch("UIDContainer.php", { cache: "no-store" });
                if (!uidResponse.ok) {
                    throw new Error(`Failed to fetch UID: ${uidResponse.statusText}`);
                }
                const uid = (await uidResponse.text()).trim();

                // If the UID has changed, fetch the new results
                if (uid && uid !== lastUID) {
                    lastUID = uid;
                    document.getElementById("uid").innerText = uid;

                    // Fetch health readings for the new UID
                    const resultsResponse = await fetch(`fetch_results.php?uid=${encodeURIComponent(uid)}`);
                    if (!resultsResponse.ok) {
                        throw new Error(`Failed to fetch results: ${resultsResponse.statusText}`);
                    }

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
            tableBody.innerHTML = ""; // Clear existing rows
            dropdown.innerHTML = '<option value="all">All Results</option><option value="select">Select Result</option>'; // Reset dropdown

            if (results.length > 0) {
                results.forEach((reading, index) => {
                    const row = document.createElement("tr");
                    row.id = `row-${reading.count}`; // Assign a unique ID to the row

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

                    // Add row to dropdown
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
            cell.classList.toggle("expanded");
            button.textContent = cell.classList.contains("expanded") ? "Read Less" : "Read More";
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
            dropdown.value = "all"; // Reset to "All Results"
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
                            selectedRows.add(i - 1); // Convert to zero-based index
                        }
                    }
                } else {
                    const num = parseInt(part.trim(), 10);
                    if (!isNaN(num)) {
                        selectedRows.add(num - 1); // Convert to zero-based index
                    }
                }
            });

            return Array.from(selectedRows).sort((a, b) => a - b); // Return sorted array
        }

        function displayResults(selection, inputValue = "") {
            const tableBody = document.querySelector("#resultsTable tbody");
            const allRows = Array.from(tableBody.children);

            // Clear all highlights
            allRows.forEach(row => row.classList.remove("highlighted"));

            if (selection === "all") {
                allRows.forEach(row => {
                    row.style.display = "table-row";
                });
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
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `count=${count}`
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The row has been deleted.',
                        });

                        // Remove the row from the table dynamically
                        const row = document.getElementById(`row-${count}`);
                        if (row) {
                            row.remove();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete the row.',
                        });
                    }
                } catch (error) {
                    console.error("Error deleting row:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while trying to delete the row.',
                    });
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

            if (selectedValue === "all") {
                // Print all rows with spacing between rows
                contentToPrint = `<h1>Diagnosis Results</h1>`;
                allRows.forEach((row, index) => {
                    const diagnosisCell = row.querySelector(".diagnosis-cell .full-text") || row.querySelector(".diagnosis-cell .short-text");
                    const diagnosis = diagnosisCell ? diagnosisCell.textContent : "No diagnosis available";

                    const createdAt = row.children[7].textContent;
                    const uid = document.getElementById("uid").textContent;

                    contentToPrint += `
                        <div style="margin-bottom: 20px; text-align: justify;">
                            <p><strong>Result #${index + 1}</strong></p>
                            <p><strong>UID:</strong> ${uid}</p>
                            <p><strong>Date:</strong> ${createdAt}</p>
                            <hr>
                            <p>${diagnosis}</p>
                        </div>
                    `;
                });
            } else if (selectedValue === "select") {
                // Print selected rows from input box
                const selectedRows = parseInput(inputBox.value);

                contentToPrint = `<h1>Diagnosis Results</h1>`;
                selectedRows.forEach(index => {
                    if (index >= 0 && index < allRows.length) {
                        const row = allRows[index];
                        const diagnosisCell = row.querySelector(".diagnosis-cell .full-text") || row.querySelector(".diagnosis-cell .short-text");
                        const diagnosis = diagnosisCell ? diagnosisCell.textContent : "No diagnosis available";

                        const createdAt = row.children[7].textContent;
                        const uid = document.getElementById("uid").textContent;

                        contentToPrint += `
                            <div style="margin-bottom: 20px; text-align: justify;">
                                <p><strong>Result #${index + 1}</strong></p>
                                <p><strong>UID:</strong> ${uid}</p>
                                <p><strong>Date:</strong> ${createdAt}</p>
                                <hr>
                                <p>${diagnosis}</p>
                            </div>
                        `;
                    }
                });
            } else {
                // Print only the selected row
                const selectedRow = allRows[selectedValue];
                const diagnosisCell = selectedRow.querySelector(".diagnosis-cell .full-text") || selectedRow.querySelector(".diagnosis-cell .short-text");
                const diagnosis = diagnosisCell ? diagnosisCell.textContent : "No diagnosis available";

                const createdAt = selectedRow.children[7].textContent;
                const uid = document.getElementById("uid").textContent;

                contentToPrint = `
                    <h1>Diagnosis Result</h1>
                    <div style="text-align: justify;">
                        <p><strong>UID:</strong> ${uid}</p>
                        <p><strong>Date:</strong> ${createdAt}</p>
                        <hr>
                        <p>${diagnosis}</p>
                    </div>
                `;
            }

            // Open a new window for printing
            const printWindow = window.open("", "_blank");
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Diagnosis</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                line-height: 1.6;
                            }
                            h1 {
                                text-align: center;
                                margin-bottom: 20px;
                            }
                            p {
                                margin: 10px 0;
                            }
                            hr {
                                margin: 20px 0;
                                border: none;
                                border-top: 1px solid #ddd;
                            }
                            div {
                                margin-bottom: 30px;
                                text-align: justify;
                            }
                            footer {
                                text-align: center;
                                margin-top: 30px;
                                font-size: 14px;
                                color: #555;
                            }
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

            // Reapply highlights after printing
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
            // Clear the UIDContainer.php file
            fetch('UIDContainer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'clear=true'
            }).then(() => {
                // Redirect to live reading page
                window.location.href = 'live reading.php';
            }).catch(error => {
                console.error('Error clearing user details:', error);
            });
        }

        // Fetch UID and results every 2 seconds
        setInterval(fetchUIDAndResults, 2000);

        // Add event listener for Enter key to trigger printing
        document.addEventListener("DOMContentLoaded", () => {
            resetInactivityTimer(); // Start the inactivity timer on page load

            // Reset the timer on user interaction
            ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, resetInactivityTimer);
            });

            const inputBox = document.getElementById("multiSelectInput");
            const printButton = document.querySelector(".print-controls button");

            // Trigger printing when Enter key is pressed
            inputBox.addEventListener("keypress", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault(); // Prevent default behavior
                    printSelectedRow(); // Trigger the print function
                }
            });

            // Trigger printing when the Print button is clicked
            printButton.addEventListener("click", (event) => {
                event.preventDefault(); // Prevent default behavior
                printSelectedRow(); // Trigger the print function
            });
        });
    </script>
</head>
<body>
    <header class="bg-green-700 text-white py-4 text-center">
        <h1 class="text-2xl font-bold">
            Here Are Your Results From Using 
            <a href="index.php" class="underline hover:text-gray-300">AI-Vital</a>
        </h1>
    </header>
    <div class="container">
        <h2 id="pageHeading" class="enlarged-heading"><?php echo htmlspecialchars($patientName); ?>'s Results</h2>
        <p class="uid-display enlarged-uid">UID: <span id="uid"><?php echo htmlspecialchars($uid); ?></span></p>

        <table id="resultsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Temperature (°C)</th>
                    <th>ECG Rate</th>
                    <th>Pulse Rate (BPM)</th>
                    <th>SpO₂ (%)</th>
                    <th>Blood Pressure</th>
                    <th>Diagnosis</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($readings)): ?>
                    <?php foreach ($readings as $index => $reading): ?>
                        <tr id="row-<?php echo $reading['count']; ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($reading['temperature']); ?></td>
                            <td><?php echo htmlspecialchars($reading['ecg_rate']); ?></td>
                            <td><?php echo htmlspecialchars($reading['pulse_rate']); ?></td>
                            <td><?php echo htmlspecialchars($reading['spo2_level']); ?></td>
                            <td><?php echo htmlspecialchars($reading['blood_pressure']); ?></td>
                            <td class="diagnosis-cell">
                                <span class="short-text"><?php echo htmlspecialchars(substr($reading['diagnosis'], 0, 50)); ?>...</span>
                                <span class="full-text"><?php echo htmlspecialchars($reading['diagnosis']); ?></span>
                                <?php if (strlen($reading['diagnosis']) > 50): ?>
                                    <span class="expand-btn" onclick="toggleDiagnosis(this)">Read More</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($reading['created_at']); ?></td>
                            <td>
                                <button onclick="deleteRow(<?php echo $reading['count']; ?>)" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No health readings found for this UID.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="print-controls">
            <select id="rowSelector" onchange="handleDropdownChange()">
                <option value="all">All Results</option>
                <option value="select">Select Result</option>
                <?php foreach ($readings as $index => $reading): ?>
                    <option value="<?php echo $index; ?>">Result #<?php echo $index + 1; ?></option>
                <?php endforeach; ?>
            </select>
            <input id="multiSelectInput" type="text" placeholder="Enter result numbers (e.g., 1,2)" style="display: none;" onblur="handleInputBlur()" oninput="handleInputChange()" />
            <button onclick="printSelectedRow()" class="button print">Print</button>
            <button onclick="clearUserDetailsAndRedirect()" class="button logout">Log Out</button>
        </div>
    </div>

    <!-- Sticky Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> Einsbern System. All rights reserved.</p>
    </footer>
</body>
</html>
