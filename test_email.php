<?php
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$logFile = __DIR__ . '/mail_debug.log';
$timestamp = date('Y-m-d H:i:s');

// Log test start
file_put_contents($logFile, "[$timestamp] Starting email test\n", FILE_APPEND);

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'einsbernsystem@gmail.com';
    $mail->Password = 'bdov zsdz sidj bcsc';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Debug output
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = function($str, $level) use ($logFile) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] Level $level: $str\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    };
    
    // Recipients
    $mail->setFrom('einsbernsystem@gmail.com', 'AI-Vital Test');
    $mail->addAddress('jampollegaspi18@gmail.com', 'Test User');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from AI-Vital';
    $mail->Body = 'This is a test email to verify the email configuration.';
    
    // Send email
    file_put_contents($logFile, "[$timestamp] Attempting to send test email\n", FILE_APPEND);
    $mail->send();
    file_put_contents($logFile, "[$timestamp] Test consult email sent successfully\n", FILE_APPEND);
    
    echo "Test email sent successfully. Check mail_debug.log for details.";
} catch (Exception $e) {
    $errorMessage = "[$timestamp] Error sending test email: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $errorMessage, FILE_APPEND);
    echo "Error sending test email. Check mail_debug.log for details.";
} 