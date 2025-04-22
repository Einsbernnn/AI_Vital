<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['clerk1_logged_in']) && !isset($_SESSION['clerk2_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
$Write="<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title>User Data - Einsbern System</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS file -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #d4fc79, #96e6a1);
            margin: 0;
            padding: 0;
            animation: fadeIn 1.5s ease-in-out;
            text-align: center;
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
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            animation: popIn 0.5s ease-out forwards;
        }
        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .table th {
            background-color: #2E8B57;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            white-space: nowrap; /* Prevents wrapping */
        }
        .table th:last-child, .table td:last-child {
            width: 180px; /* Fixed width for action buttons */
        }
        .btn-success, .btn-danger {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            display: inline-block;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
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
    </style>
</head>
<body>
    <div class="nav-bar">
        <div>
            <a href="index.php">Home</a>
            <a href="user data.php" class="active">User Data</a>
            <a href="registration.php">Register</a>
            <a href="read tag.php">Read Tag</a>
            <a href="about.php">About Us</a>
            <a href="live reading.php">Live-Reading</a>
            <a href="results.php">Results</a>
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
        <h2>Registered Users</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Mobile Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'database.php';
                $pdo = Database::connect();
                $sql = 'SELECT * FROM health_diagnostics ORDER BY name ASC';
                foreach ($pdo->query($sql) as $row) {
                    echo '<tr>';
                    echo '<td>'. $row['name'] . '</td>';
                    echo '<td>'. $row['id'] . '</td>';
                    echo '<td>'. $row['gender'] . '</td>';
                    echo '<td>'. $row['email'] . '</td>';
                    echo '<td>'. $row['mobile'] . '</td>';
                    echo '<td>
                            <div class="action-buttons">
                                <a class="btn-success" href="user data edit page.php?id='.$row['id'].'">Edit</a>
                                <a class="btn-danger" href="user data delete page.php?id='.$row['id'].'">Delete</a>
                            </div>
                          </td>';
                    echo '</tr>';
                }
                Database::disconnect();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>