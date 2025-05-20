<?php
    require 'database.php';
    
    $id = $_GET['id'] ?? null;
    if ($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM health_diagnostics WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
    }

    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM health_diagnostics WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        Database::disconnect();
        
        header("Location: userdata2.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS file -->
    <title>Delete User - Einsbern System</title>
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
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .nav-bar a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 18px;
            cursor: pointer;
            padding: 12px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .nav-bar a.active {
            background: black;
        }
        .nav-bar a:hover {
            background: #1E6B47;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .container h2 {
            margin-bottom: 20px;
            color: #2E8B57;
        }
        .alert {
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn-danger, .btn-success {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger:hover, .btn-success:hover {
            opacity: 0.9;
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
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <script>
        function confirmDelete(event) {
            if (!confirm("Are you sure you want to delete this user? This action cannot be undone.")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <div class="nav-bar">
        <a href="index.php">Home</a>
        <a href="userdata2.php" class="active">User Data</a>
        <a href="registration2.php">Register</a>
        <a href="read tag.php">Read Tag</a>
        <a href="about.php">About Us</a>
        <a href="live reading.php">Live-Reading</a>
    </div>
    <div class="container">
        <h2>Delete User</h2>
        <p class="alert">Are you sure you want to delete this user? This action cannot be undone.</p>
        <?php if ($data): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Mobile</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['gender'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>
        <form action="user data delete page.php" method="post" onsubmit="confirmDelete(event)">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"/>
            <div class="form-actions">
                <button type="submit" class="btn-danger">Yes</button>
                <a class="btn-success" href="userdata2.php">No</a>
            </div>
        </form>
    </div>
</body>
</html>