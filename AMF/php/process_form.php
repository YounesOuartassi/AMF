<?php
// Include PHPMailer classes into the global namespace
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    // Validate the data
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "All fields are required.";
        exit;
    }

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                          // Set mailer to use SMTP
        $mail->Host       = 'smtp.mailgun.org';                   // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                                 // Enable SMTP authentication
        $mail->Username   = 'postmaster@your-domain.com';         // SMTP username (Mailgun SMTP login)
        $mail->Password   = 'sandbox1765fa18b5a74ce79cd376d18275ed04.mailgun.org';               // SMTP password (Mailgun SMTP password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Enable TLS encryption, `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                  // TCP port to connect to

        // Recipients
        $mail->setFrom('noreply@your-domain.com', $name);         // From email and name
        $mail->addAddress('recipient@example.com');               // Add a recipient

        // Content
        $mail->isHTML(false);                                     // Set email format to plain text
        $mail->Subject = $subject;
        $mail->Body    = "Name: $name\nEmail: $email\nSubject: $subject\n\n$message";

        // Send the email
        $mail->send();
        echo 'Message sent successfully.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
