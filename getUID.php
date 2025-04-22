<?php
$UIDresult = $_POST["UIDresult"] ?? null;

if ($UIDresult) {
    $Write = "<?php $" . "UIDresult='" . $UIDresult . "'; " . "echo $" . "UIDresult;" . " ?>";
    file_put_contents('UIDContainer.php', $Write);
    echo "UID received and written successfully.";
} else {
    error_log("No UID received from ESP32.");
    echo "Error: No UID received.";
}
?>