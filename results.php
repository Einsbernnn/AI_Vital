<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['clerk1_logged_in']) && !isset($_SESSION['clerk2_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
include 'database.php'; // Include your database connection file
$pdo = Database::connect();

// Sorting and Filtering
$order = $_GET['order'] ?? 'num';
$sort = $_GET['sort'] ?? 'ASC';
$search = $_GET['search'] ?? ''; // Get search input
$date = $_GET['date'] ?? ''; // Get date input

$validColumns = ['num', 'id', 'body_temperature', 'ecg', 'pulse_rate', 'spo2', 'diagnostic_result', 'timestamp'];
if (!in_array($order, $validColumns)) {
    $order = 'num';
}
$sort = ($sort === 'DESC') ? 'DESC' : 'ASC';

// Query with sorting, searching, and filtering by date
$sql = "SELECT num, id, body_temperature, ecg, pulse_rate, spo2, diagnostic_result, timestamp 
        FROM table_the_sensors 
        WHERE (num LIKE ? OR id LIKE ? OR body_temperature LIKE ? OR ecg LIKE ? OR pulse_rate LIKE ? OR spo2 LIKE ? OR diagnostic_result LIKE ? OR timestamp LIKE ?)
        AND (timestamp LIKE ?)
        ORDER BY $order $sort";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge(array_fill(0, 8, "%$search%"), ["%$date%"]));

$datesQuery = "SELECT DISTINCT DATE(timestamp) as date FROM table_the_sensors ORDER BY date DESC";
$datesStmt = $pdo->prepare($datesQuery);
$datesStmt->execute();
$dates = $datesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get the current month and year
$currentMonth = $_GET['month'] ?? date('Y-m');
$firstDayOfMonth = date('w', strtotime("$currentMonth-01"));
$daysInMonth = date('t', strtotime($currentMonth));
$resultsDates = array_column($dates, 'date'); // Extract dates with results
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #d4fc79, #96e6a1);
            text-align: center;
            margin: 0;
            animation: fadeIn 1.5s ease-in-out;
        }
        .nav-bar {
            background: #2E8B57;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        .nav-bar a {
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: bold;
            font-size: 16px;
        }
        .nav-bar a.active {
            background: black;
        }
        .nav-bar a:hover {
            background: #1E6B47;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            animation: popIn 0.5s ease-out forwards;
        }
        .search-bar {
            margin-bottom: 20px;
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
            cursor: pointer;
        }
        th a {
            color: white;
            text-decoration: none;
        }
        th a:hover {
            text-decoration: underline;
        }
        .date-buttons {
            margin-top: 20px;
        }
        .date-buttons button {
            margin: 5px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .date-buttons button:hover {
            background-color: #1E6B47;
        }
        .admin-profile {
            position: absolute;
            right: 20px;
            top: 15px;
        }
        .admin-profile-content {
            display: none;
            position: absolute;
            right: 0;
            background: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: left;
            z-index: 1;
        }
        .admin-profile:hover .admin-profile-content {
            display: block;
        }
        .admin-profile h4 {
            margin: 0 0 10px 0;
            color: #2E8B57;
        }
        .admin-profile p {
            margin: 5px 0;
            color: #444;
        }
        .logout-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .logout-button:hover {
            background: #c82333;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .calendar-container {
            margin: 20px auto;
            max-width: 400px;
            text-align: center;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .calendar div {
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .calendar .header {
            font-weight: bold;
            background-color: #28a745;
            color: white;
        }
        .calendar .day {
            background-color: #e9ecef;
        }
        .calendar .has-result {
            background-color: #ffffcc; /* Highlight dates with results */
            border: 2px solid #28a745;
            border-radius: 50%;
        }
        .calendar-navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .calendar-navigation button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .calendar-navigation button:hover {
            background-color: #1E6B47;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div>
            <a href="index.php">Home</a>
            <a href="userdata2.php">User Data</a>
            <a href="registration2.php">Registration</a>
            <a href="read tag.php">Read Tag ID</a>
            <a href="about.php">About Us</a>
            <a href="live reading.php">Live-Reading</a>
            <a class="active" href="results2.php">Results</a>
        </div>
        <?php if (isset($_SESSION['admin_logged_in']) || isset($_SESSION['clerk1_logged_in']) || isset($_SESSION['clerk2_logged_in'])) { ?>
            <div class="admin-profile">
                <button class="logout-button">Profile</button>
                <div class="admin-profile-content">
                    <h4>Admin Profile</h4>
                    <p>Username: <?= $_SESSION['admin_logged_in'] ? 'Admin' : ($_SESSION['clerk1_logged_in'] ? 'Clerk 1' : 'Clerk 2') ?></p>
                    <p>Name: John Doe</p> <!-- Example name, replace with actual data if available -->
                    <p>Email: admin@example.com</p> <!-- Example email, replace with actual data if available -->
                    <button class="logout-button" onclick="window.location.href='logout.php'">Logout</button>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <div class="container">
        <h2 class="text-center">Vital Sign Results</h2>
        
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search Here" value="<?= htmlspecialchars($search) ?>">
            <select name="date" onchange="this.form.submit()">
                <option value="">All Results</option>
                <?php foreach ($dates as $dateRow) { ?>
                    <option value="<?= $dateRow['date'] ?>" <?= ($dateRow['date'] == $date) ? 'selected' : '' ?>><?= $dateRow['date'] ?></option>
                <?php } ?>
            </select>
            <button type="submit">Search</button>
            <button type="button" onclick="if (document.querySelector('select[name=date]').value) { window.print(); } else { alert('Please select a date to print.'); }">Print</button>
        </form>
        
        <table>
            <tr>
                <th>Num#</th>
                <th>ID</th>
                <th>Body Temperature (°C)</th>
                <th>ECG</th>
                <th>Pulse Rate (BPM)</th>
                <th>SpO2 (%)</th>
                <th>Diagnostic Result</th>
                <th>Recorded At</th>
            </tr>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= $row['num'] ?></td>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['body_temperature'] ?></td>
                    <td><?= $row['ecg'] ?></td>
                    <td><?= $row['pulse_rate'] ?></td>
                    <td><?= $row['spo2'] ?></td>
                    <td><?= $row['diagnostic_result'] ?></td>
                    <td><?= $row['timestamp'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="calendar-container">
        <div class="calendar-navigation">
            <form method="GET" style="display: inline;">
                <input type="hidden" name="month" value="<?= date('Y-m', strtotime("$currentMonth -1 month")) ?>">
                <button type="submit">Previous</button>
            </form>
            <h3><?= date('F Y', strtotime($currentMonth)) ?></h3>
            <form method="GET" style="display: inline;">
                <input type="hidden" name="month" value="<?= date('Y-m', strtotime("$currentMonth +1 month")) ?>">
                <button type="submit">Next</button>
            </form>
        </div>
        <div class="calendar">
            <div class="header">Sun</div>
            <div class="header">Mon</div>
            <div class="header">Tue</div>
            <div class="header">Wed</div>
            <div class="header">Thu</div>
            <div class="header">Fri</div>
            <div class="header">Sat</div>
            <?php
            // Fill empty days before the first day of the month
            for ($i = 0; $i < $firstDayOfMonth; $i++) {
                echo '<div class="day"></div>';
            }

            // Fill days of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = "$currentMonth-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $hasResultClass = in_array($date, $resultsDates) ? 'has-result' : '';
                echo "<div class='day $hasResultClass'>$day</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
