<?php
require 'vendor/autoload.php'; // Ensure PHPMailer is loaded

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        $clean_str = trim(strip_tags($str));
        if (!empty($clean_str)) {
            echo date('Y-m-d H:i:s ') . $clean_str . "<br>\n";
        }
    };

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    
    // Gmail credentials
    $mail->Username = 'ehalal.btechenyo@gmail.com';
    $mail->Password = 'slziwpnjnlxuyiuj'; // Your App Password
    
    // Security settings
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Set timeout value
    $mail->Timeout = 60; // 60 seconds timeout
    $mail->SMTPKeepAlive = true; // Keep the connection alive
    
    // Clear any stored addresses
    $mail->clearAddresses();
    $mail->clearReplyTos();

    // Set character encoding
    $mail->CharSet = 'UTF-8';
    
    // Recipients
    $mail->setFrom('ehalal.btechenyo@gmail.com', 'E-Halal System');
    $mail->addAddress('janneiljanzen.go@gmail.com');
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from E-Halal System';
    $mail->Body = <<<HTML
    <div style="font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #1d7c39;">E-Halal System Test Email</h2>
        <p>This is a test email to verify that the SMTP settings are working correctly.</p>
        <p>If you received this email, it means the configuration is successful!</p>
        <hr style="border: 1px solid #eee; margin: 20px 0;">
        <p style="color: #666; font-size: 12px;">This is an automated message, please do not reply.</p>
    </div>
    HTML;
    
    // Plain text version for non-HTML mail clients
    $mail->AltBody = 'This is a test email to verify that the SMTP settings are working correctly.';

    // Send the email
    if ($mail->send()) {
        echo '<div style="color: green; padding: 10px; border: 1px solid green; margin: 10px 0;">';
        echo 'Message has been sent successfully!';
        echo '</div>';
    }
} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">';
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    echo '</div>';
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

// Close SMTP connection
if ($mail->SMTPKeepAlive) {
    $mail->smtpClose();
}