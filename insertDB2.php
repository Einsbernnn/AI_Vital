<?php
require 'database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name = htmlspecialchars($_POST['name']);
    $id = htmlspecialchars($_POST['id']);
    $gender = htmlspecialchars($_POST['gender']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $mobile = htmlspecialchars($_POST['mobile']);
    $age = htmlspecialchars($_POST['age']);
    $height = htmlspecialchars($_POST['height']);
    $weight = htmlspecialchars($_POST['weight']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
                alert('Error: Invalid email format.');
                window.location.href = 'registration2.php';
              </script>";
        exit;
    }

    // Check for empty fields
    if (empty($name) || empty($id) || empty($gender) || empty($email) || empty($mobile) || empty($age) || empty($height) || empty($weight)) {
        echo "<script>
                alert('Error: All fields are required.');
                window.location.href = 'registration2.php';
              </script>";
        exit;
    }

    try {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if ID or Email is already registered
        $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM health_diagnostics WHERE id = ? OR email = ?");
        $checkQuery->execute([$id, $email]);
        $exists = $checkQuery->fetchColumn();

        if ($exists) {
            echo "<script>
                    alert('Error: The ID or Email is already registered.');
                    window.location.href = 'registration2.php';
                  </script>";
            Database::disconnect();
            exit;
        }

        // Insert data into the database
        $sql = "INSERT INTO health_diagnostics (name, id, gender, email, mobile, age, height, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $q = $pdo->prepare($sql);

        if ($q->execute([$name, $id, $gender, $email, $mobile, $age, $height, $weight])) {
            Database::disconnect();
            
            // Send confirmation email
            sendEmail($email, $name);

            // Redirect to avoid form resubmission
            header("Location: registration2.php");
            exit();
        } else {
            echo "<script>
                    alert('Error registering user.');
                    window.location.href = 'registration2.php';
                  </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Database error: " . $e->getMessage() . "');
                window.location.href = 'registration2.php';
              </script>";
    }
}

// Function to send email using PHPMailer
function sendEmail($email, $name) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // SMTP Server (Gmail)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'einsbernsystem@gmail.com'; // Your Gmail address
        $mail->Password   = 'bdov zsdz sidj bcsc';    // Gmail App Password (Not your actual password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & Recipient
        $mail->setFrom('einsbernsystem@gmail.com', 'The Einsbern System');
        $mail->addAddress($email, $name); // Send to the registered user

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Thank You for Registering to AI-Vital System!';
        $mail->Body    = "<h3>Welcome, $name!</h3><p>Thank you for registering with AI-Vital System. Your details have been successfully saved. You can now use our systems features to get your health diagnosis -EinsbernSystem.</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
    }
}
?>