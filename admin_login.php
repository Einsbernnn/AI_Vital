<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VitaSign</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #d4fc79, #96e6a1);
            text-align: center;
            margin: 0;
            padding: 0;
            animation: fadeIn 1.5s ease-in-out;
        }
        .nav-bar {
            background: rgba(46, 139, 87, 0.7); /* Add transparency */
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
            max-width: 400px;
            margin: 100px auto;
            background: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        h2 {
            color: #2E8B57;
            margin-bottom: 20px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .btn-container {
            margin-top: 20px;
        }
        .btn-login {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #218838;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div>
            <a class="active" href="index.php">Home</a>
            <a href="userdata2.php">User Data</a>
            <a href="registration2.php">Registration</a>
            <a href="read tag.php">Read Tag ID</a>
            <a href="about.php">About Us</a>
            <a href="live reading.php">Live-Reading</a>
            <a href="results2.php">Results</a>
        </div>
    </div>
    <div class="container">
        <h2>Are you an Admin?</h2>
        <form action="admin_login_process.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="btn-container">
                <button type="submit" class="btn-login">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
