<?php
    $Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
    file_put_contents('UIDContainer.php', $Write);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <script src="jquery.min.js"></script>
    <title>Read Tag : NodeMCU V3 ESP8266 / ESP12E with MYSQL Database</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #d4fc79, #96e6a1);
            text-align: center;
            margin: 0;
            animation: fadeIn 1.5s ease-in-out;
        }

        /* Fix for the navigation bar */
        .nav-bar {
            background: #2E8B57;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: center;
            gap: 15px;
            align-items: center;
            position: relative;
            z-index: 1;
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
            max-width: 800px;
            margin: 50px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            animation: popIn 0.5s ease-out forwards;
        }

        h2, h3 {
            color: #2E8B57;
        }

        .hero {
            padding: 50px 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 20px;
            animation: slideIn 1s ease-in-out;
        }

        .hero h1 {
            font-size: 32px;
            font-weight: 600;
            color: #2E8B57;
        }

        .hero p {
            font-size: 18px;
            color: #444;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        ul.topnav {
            list-style-type: none;
            margin: auto;
            padding: 0;
            overflow: hidden;
            background-color: #4CAF50;
            width: 70%;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        ul.topnav li {
            display: inline;
        }

        ul.topnav li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        ul.topnav li a:hover:not(.active) { background-color: #3e8e41; }

        ul.topnav li a.active { background-color: #333; }

        ul.topnav li.right { float: right; }

        @media screen and (max-width: 600px) {
            ul.topnav li.right, ul.topnav li { float: none; }
        }

        td.lf {
            padding-left: 15px;
            padding-top: 12px;
            padding-bottom: 12px;
        }
    </style>
</head>

<body>
<div class="nav-bar">
		<a href="index.php">Home</a>
        <a href="userdata2.php">User Data</a>
        <a href="registration2.php">Registration</a>
        <a class="active" href="read tag.php">Read Tag ID</a>
        <a href="about.php">About Us</a>
        <a href="live reading.php">Live-Reading</a>
        <a href="my_results.php">Results</a>
    </div>

    <div class="container">
        <h2>NodeMCU V3 ESP8266 / ESP12E with MYSQL Database</h2>
        <div class="hero">
            <h1>Welcome to Vitals Sign Monitoring System</h1>
            <p>Real-time monitoring of vital signs with the power of ESP8266 and MySQL integration.</p>
        </div>

        <h3 id="blink">Please Scan Tag to Display ID or User Data</h3>
        <p id="getUID" hidden></p>

        <div id="show_user_data">
            <form>
                <table  width="452" border="1" bordercolor="#10a0c5" align="center" cellpadding="0" cellspacing="1" bgcolor="#000" style="padding: 2px">
                    <tr>
                        <td height="40" align="center" bgcolor="#10a0c5"><font color="#FFFFFF"><b>User Data</b></font></td>
                    </tr>
                    <tr>
                        <td bgcolor="#f9f9f9">
                            <table width="452" border="0" align="center" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td width="113" align="left" class="lf">ID</td>
                                    <td style="font-weight:bold">:</td>
                                    <td align="left">--------</td>
                                </tr>
                                <tr bgcolor="#f2f2f2">
                                    <td align="left" class="lf">Name</td>
                                    <td style="font-weight:bold">:</td>
                                    <td align="left">--------</td>
                                </tr>
                                <tr>
                                    <td align="left" class="lf">Gender</td>
                                    <td style="font-weight:bold">:</td>
                                    <td align="left">--------</td>
                                </tr>
                                <tr bgcolor="#f2f2f2">
                                    <td align="left" class="lf">Email</td>
                                    <td style="font-weight:bold">:</td>
                                    <td align="left">--------</td>
                                </tr>
                                <tr>
                                    <td align="left" class="lf">Mobile Number</td>
                                    <td style="font-weight:bold">:</td>
                                    <td align="left">--------</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

    <script>
        var myVar = setInterval(myTimer, 1000);
        var myVar1 = setInterval(myTimer1, 1000);
        var oldID="";
        clearInterval(myVar1);

        function myTimer() {
            var getID=document.getElementById("getUID").innerHTML;
            oldID=getID;
            if(getID!="") {
                myVar1 = setInterval(myTimer1, 500);
                showUser(getID);
                clearInterval(myVar);
            }
        }

        function myTimer1() {
            var getID=document.getElementById("getUID").innerHTML;
            if(oldID!=getID) {
                myVar = setInterval(myTimer, 500);
                clearInterval(myVar1);
            }
        }

        function showUser(str) {
            if (str == "") {
                document.getElementById("show_user_data").innerHTML = "";
                return;
            } else {
                if (window.XMLHttpRequest) {
                    xmlhttp = new XMLHttpRequest();
                } else {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("show_user_data").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("GET","read tag user data.php?id="+str,true);
                xmlhttp.send();
            }
        }

        var blink = document.getElementById('blink');
        setInterval(function() {
            blink.style.opacity = (blink.style.opacity == 0 ? 1 : 0);
        }, 750);
    </script>
</body>
</html>