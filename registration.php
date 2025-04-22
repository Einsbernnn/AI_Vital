<?php
session_start();
$Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="styles.css"> 
    <script src="jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            $("#getUID").load("UIDContainer.php");
            setInterval(function() {
                $("#getUID").load("UIDContainer.php");
            }, 500);
        });
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #d4fc79, #96e6a1);
            margin: 0;
            padding: 0;
            animation: fadeIn 1.5s ease-in-out;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            text-align: center;
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
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        textarea {
            resize: none;
            height: 40px;
        }
        .nav-bar {
            background: #2E8B57;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: center;
            align-items: center;
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
            display: inline-block; /* Ensures proper alignment */
        }
        .nav-bar a.active {
            background: black;
            font-weight: bold;
        }
        .nav-bar a:hover {
            background: #1E6B47;
        }
        .btn-save {
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
        .btn-save:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div>
            <a href="index.php">Home</a>
            <a href="userdata2.php">User Data</a>
            <a href="registration2.php" class="active">Registration</a>
            <a href="read tag.php">Read Tag ID</a>
            <a href="about.php">About Us</a>
            <a href="live reading.php">Live-Reading</a>
            <a href="results2.php">Results</a>
        </div>
    </div>
    <div class="container">
        <h2>Registration Form</h2>
        <form action="insertDB.php" method="post">
            <div class="form-group">
                <label for="id">ID</label>
                <textarea name="id" id="getUID" placeholder="Please Scan your Card / Key Chain to display ID" required></textarea>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" oninput="this.value = this.value.toUpperCase();" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Rather not say">Rather not say</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" pattern="[a-zA-Z0-9._%+-]+@gmail\.com" required>
            </div>
            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="text" name="mobile" id="mobile" pattern="[0-9]{11}" maxlength="11" required>
            </div>
            <button type="submit" class="btn-save">Save</button>
        </form>
    </div>
</body>
</html>
<?php
include 'footer.php'; // Include your footer file if available
?>
