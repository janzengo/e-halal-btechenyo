<?php
require_once __DIR__ . '/../../classes/Database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader if available
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    // Manual includes if autoloader is not available
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
}

class AdminOTPMailer {
    private $db;
    private $otp_length = 6;
    private $otp_expiry_minutes = 10; // OTP expires after 10 minutes
    private $mailer;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
        date_default_timezone_set('Asia/Manila');
    }

    private function setupMailer() {
        // Server settings
        $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $this->mailer->Port = $_ENV['MAIL_PORT'];
        
        // Additional settings
        $this->mailer->Timeout = 60;
        $this->mailer->SMTPKeepAlive = true;
        $this->mailer->CharSet = 'UTF-8';
        
        // Set default from address
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME'] ?? 'E-Halal BTECHenyo System');
    }

    /**
     * Generate a random OTP
     * 
     * @return string Generated OTP
     */
    private function generateOTP() {
        $digits = '0123456789';
        $otp = '';
        
        for ($i = 0; $i < $this->otp_length; $i++) {
            $otp .= $digits[rand(0, 9)];
        }
        
        return $otp;
    }

    /**
     * Store OTP in database
     * 
     * @param string $email Admin's email
     * @param string $otp Generated OTP
     * @return bool Success of operation
     */
    private function storeOTP($email, $otp) {
        $expiry_time = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        try {
            // Delete any existing OTPs for this admin
            $stmt = $this->db->prepare("DELETE FROM admin_otp_requests WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            // Insert new OTP
            $stmt = $this->db->prepare(
                "INSERT INTO admin_otp_requests (email, otp, expires_at, attempts) 
                 VALUES (?, ?, ?, 0)"
            );
            
            $stmt->bind_param("sss", $email, $otp, $expiry_time);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error storing admin OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if there's a valid OTP request
     * 
     * @param string $email Admin's email
     * @return bool Whether a valid OTP request exists
     */
    public function hasValidOTPRequest($email) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM admin_otp_requests 
                WHERE email = ? AND expires_at > NOW() AND attempts < 5");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error checking OTP request: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate an OTP
     * 
     * @param string $email Admin's email
     * @param string $otp OTP to validate
     * @return array Status and validation result
     */
    public function validateOTP($email, $otp) {
        // Get current OTP data
        $stmt = $this->db->prepare("SELECT otp, attempts FROM admin_otp_requests WHERE email = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $otpData = $result->fetch_assoc();

        if (!$otpData) {
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }

        if ($otpData['attempts'] >= 5) {
            return ['success' => false, 'message' => 'Maximum attempts reached.'];
        }

        if ($otpData['otp'] === $otp) {
            return ['success' => true];
        }

        // Increment attempts
        $this->incrementAttempts($email);
        $remaining = $this->getRemainingAttempts($email);

        return [
            'success' => false,
            'message' => 'Invalid OTP code. ' . ($remaining > 0 ? "You have {$remaining} attempts remaining." : "Maximum attempts reached."),
            'remaining_attempts' => $remaining
        ];
    }

    /**
     * Delete OTP after use or when generating a new one
     * 
     * @param string $email Admin's email
     * @return bool Success of operation
     */
    public function deleteOTP($email) {
        try {
            $stmt = $this->db->prepare("DELETE FROM admin_otp_requests WHERE email = ?");
            $stmt->bind_param("s", $email);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via email
     * 
     * @param string $email Recipient email address
     * @param string $otp OTP to send
     * @param string $name Recipient's name
     * @return array Status and message
     */
    private function sendOTPEmail($email, $otp, $name = '') {
        $this->mailer->clearAllRecipients();
        
        try {
            // Recipients
            $this->mailer->setFrom($_ENV['MAIL_USERNAME'], 'E-Halal BTECHenyo System');
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Admin Login OTP Verification - E-Halal BTECHenyo';
            
            $mailBody = $this->buildEmailTemplate($otp, $name);
            $this->mailer->Body = $mailBody;
            
            $this->mailer->AltBody = "Your admin verification code is: $otp\nThis code will expire in {$this->otp_expiry_minutes} minutes.\nDo not share this code with anyone.";
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => "OTP sent successfully to $email"
            ];
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $this->mailer->ErrorInfo);
            return [
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"
            ];
        }
    }

    /**
     * Generate and send a new OTP
     * 
     * @param string $email Admin's email
     * @param string $name Admin's name (optional)
     * @return array Status and message
     */
    public function generateAndSendOTP($email, $name = '') {
        try {
            // Generate new OTP
            $otp = $this->generateOTP();
            
            // Store OTP in database
            if (!$this->storeOTP($email, $otp)) {
                throw new Exception("Failed to store OTP");
            }
            
            // Send OTP via email
            return $this->sendOTPEmail($email, $otp, $name);
            
        } catch (Exception $e) {
            error_log("Error generating/sending OTP: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to generate and send OTP: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create email template for OTP
     * 
     * @param string $otp The OTP code
     * @param string $name Recipient's name
     * @return string HTML email template
     */
    private function buildEmailTemplate($otp, $name) {
        $greeting = empty($name) ? "Hello Administrator" : "Hello $name";
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin OTP Verification</title>
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
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    background-color: #f9f9f9;
                    padding: 20px;
                    border-radius: 0 0 5px 5px;
                }
                .otp-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1d7c39;
                    text-align: center;
                    margin: 20px 0;
                    letter-spacing: 5px;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    margin-top: 20px;
                }
                .timer {
                    text-align: center;
                    color: #d9534f;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>E-Halal BTECHenyo Admin Portal</h1>
                </div>
                <div class="content">
                    <p>$greeting,</p>
                    <p>You have requested an OTP (One-Time Password) for accessing the E-Halal BTECHenyo Admin Portal. Your verification code is:</p>
                    
                    <div class="otp-code">$otp</div>
                    
                    <div class="timer">This code will expire in {$this->otp_expiry_minutes} minutes</div>
                    
                    <p>If you did not request this code, please contact the system administrator immediately.</p>
                    
                    <p>Do not share this OTP with anyone. Our staff will never ask for your OTP.</p>
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

    public function getOTPAttempts($email) {
        $stmt = $this->db->prepare("SELECT attempts, expires_at FROM admin_otp_requests WHERE email = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function incrementAttempts($email) {
        $stmt = $this->db->prepare("UPDATE admin_otp_requests SET attempts = attempts + 1 WHERE email = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }

    public function getRemainingAttempts($email) {
        $stmt = $this->db->prepare("SELECT 5 - attempts as remaining FROM admin_otp_requests WHERE email = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data ? $data['remaining'] : 0;
    }
} 