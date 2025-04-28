<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
}

class OfficerMailer {
    private $mail_config;

    public function __construct() {
        $this->mail_config = function_exists('mail_config') ? mail_config() : [];
        date_default_timezone_set('Asia/Manila');
    }

    public function sendPasswordEmail($email, $name, $username, $password) {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                $clean_str = trim(strip_tags($str));
                if (!empty($clean_str)) {
                    error_log(date('Y-m-d H:i:s ') . $clean_str . "\n", 3, __DIR__ . '/../logs/smtp_debug.log');
                }
            };
            if ($this->mail_config['use_smtp']) {
                $mail->isSMTP();
                $mail->Host = $this->mail_config['smtp']['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->mail_config['smtp']['username'];
                $mail->Password = $this->mail_config['smtp']['password'];
                $mail->SMTPSecure = $this->mail_config['smtp']['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $this->mail_config['smtp']['port'];
            } else {
                $mail->isMail();
            }
            $mail->Timeout = 60;
            $mail->SMTPKeepAlive = true;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($this->mail_config['mail_from'], $this->mail_config['mail_from_name']);
            $mail->addReplyTo($this->mail_config['mail_reply_to'], $this->mail_config['mail_from_name']);
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Your Officer Account Credentials - E-Halal BTECHenyo';
            $mailBody = $this->buildPasswordEmailTemplate($name, $username, $password);
            $mail->Body = $mailBody;
            $mail->AltBody = "Hello $name,\nYour officer account for E-Halal BTECHenyo has been created.\nUsername: $username\nPassword: $password\nPlease log in and change your password as soon as possible.";
            $mail->send();
            return [
                'success' => true,
                'message' => "Credentials sent successfully to $email"
            ];
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return [
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ];
        }
        if ($mail->SMTPKeepAlive) {
            $mail->smtpClose();
        }
    }

    private function buildPasswordEmailTemplate($name, $username, $password) {
        $greeting = empty($name) ? "Hello" : "Hello $name";
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Officer Account Credentials</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #1d7c39;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    background-color: #f9f9f9;
                    padding: 20px;
                    border-radius: 0 0 5px 5px;
                }
                .credentials {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1d7c39;
                    text-align: center;
                    margin: 20px 0;
                    letter-spacing: 3px;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .credentials-label {
                    font-size: 14px;
                    color: #666;
                    display: block;
                    margin-bottom: 5px;
                }
                .warning {
                    text-align: center;
                    color: #d9534f;
                    font-weight: bold;
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #fff;
                    border-left: 4px solid #d9534f;
                    border-radius: 3px;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    margin-top: 20px;
                }
                .security-note {
                    background-color: #fff3cd;
                    border: 1px solid #ffeeba;
                    color: #856404;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>E-Halal BTECHenyo</h1>
                </div>
                <div class="content">
                    <p>$greeting,</p>
                    <p>Your officer account for E-Halal BTECHenyo has been created. Please find your login credentials below:</p>
                    
                    <div class="credentials">
                        <div class="credentials-label">Username</div>
                        $username
                        <div class="credentials-label" style="margin-top: 15px;">Password</div>
                        $password
                    </div>
                    
                    <div class="warning">
                        For security reasons, please change your password immediately after your first login.
                    </div>
                    
                    <div class="security-note">
                        <strong>Important Security Notes:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Never share your credentials with anyone</li>
                            <li>Make sure to use a strong password when changing it</li>
                            <li>Log out when you're done using the system</li>
                        </ul>
                    </div>

                    <p>If you did not expect this email or believe it was sent in error, please contact the system administrator immediately.</p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 E-Halal BTECHenyo Voting System. All rights reserved.</p>
                    <p>This is an automated email, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
} 