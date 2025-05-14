<?php
// Use PHPMailer for reliable email delivery (like AI_send.php)
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$diagnosis = $data['diagnosis'] ?? '';
$name = $data['name'] ?? '';

if (!$email || !$diagnosis) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'einsbernsystem@gmail.com';
    $mail->Password = 'bdov zsdz sidj bcsc';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('einsbernsystem@gmail.com', 'AI-Vital Diagnoser');
    $mail->addAddress($email, $name ?: $email);
    $mail->isHTML(true);
    $mail->Subject = 'Your AI-VITAL Medical Diagnosis';
    $mail->Body = '<p>Hello ' . htmlspecialchars($name ?: $email) . ',</p>' .
                  '<p>Here is your AI-VITAL diagnosis result:</p>' .
                  '<div style="white-space:pre-line;">' . nl2br(htmlspecialchars($diagnosis)) . '</div>' .
                  '<p style="margin-top:20px;">Stay healthy and take care!</p>';
    $mail->AltBody = strip_tags($diagnosis);

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Mail send failed: ' . $mail->ErrorInfo]);
}
