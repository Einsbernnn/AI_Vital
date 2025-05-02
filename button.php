<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Signal to ESP32</title>
</head>
<body>
    <button onclick="sendSignal()">Press Me</button>
    <div id="responseMsg" style="margin-top:10px;color:green;"></div>
    <script>
    function sendSignal() {
        fetch('send_signal.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('responseMsg').textContent = "Signal has been sent! ESP32 response: " + data;
            })
            .catch(() => {
                document.getElementById('responseMsg').textContent = "Failed to send signal.";
            });
    }
    </script>
</body>
</html>