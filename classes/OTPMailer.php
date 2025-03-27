<?php
require_once __DIR__ . '/Database.php';
/**
 * OTPMailer Class
 * 
 * Handles OTP generation, validation, and sending via email using PHPMailer
 */

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Manual includes if autoloader is not available
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
}

class OTPMailer {
    private $db;
    private $otp_length = 6;
    private $otp_expiry_minutes = 10; // OTP expires after 10 minutes
    private $mail_config;

    public function __construct() {
        $this->db = Database::getInstance();
        // Set timezone for Philippines
        date_default_timezone_set('Asia/Manila');
        
        // Load mail configuration
        $this->mail_config = mail_config();
    }

    /**
     * Generate a random OTP of specified length
     * 
     * @return string Generated OTP
     */
    public function generateOTP() {
        // Generate a random numeric OTP
        $digits = '0123456789';
        $otp = '';
        
        for ($i = 0; $i < $this->otp_length; $i++) {
            $otp .= $digits[rand(0, 9)];
        }
        
        return $otp;
    }

    /**
     * Store OTP in database with expiration time
     * 
     * @param string $student_number Student's ID number
     * @param string $otp Generated OTP
     * @return bool Success of operation
     */
    public function storeOTP($student_number, $otp) {
        // Calculate expiry time (10 minutes from now) using proper timezone
        $expiry_time = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        try {
        // Delete any existing OTPs for this student
            $stmt = $this->db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        
            // Insert new OTP with attempts set to 0
            $stmt = $this->db->prepare(
                "INSERT INTO otp_requests (student_number, otp, expires_at, attempts) 
                 VALUES (?, ?, ?, 0)"
            );
            
        $stmt->bind_param("sss", $student_number, $otp, $expiry_time);
        
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error storing OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate an OTP for a student
     * 
     * @param string $student_number Student's ID number
     * @param string $otp OTP to validate
     * @return array Status and validation result
     */
    public function validateOTP($student_number, $otp) {
        try {
            // First check if OTP exists and is valid
            $stmt = $this->db->prepare("SELECT id, attempts FROM otp_requests 
                WHERE student_number = ? AND expires_at > NOW()");
            $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.',
                    'remaining_attempts' => 0
                ];
            }

            $row = $result->fetch_assoc();
            $current_attempts = $row['attempts'];

            // Check if already at max attempts
            if ($current_attempts >= 5) {
                // Delete the record
                $delete_stmt = $this->db->prepare("DELETE FROM otp_requests WHERE id = ?");
                $delete_stmt->bind_param("i", $row['id']);
                $delete_stmt->execute();
                
                return [
                    'success' => false,
                    'message' => 'Maximum attempts reached. Please request a new OTP.',
                    'remaining_attempts' => 0
                ];
            }

            // Verify OTP
            $verify_stmt = $this->db->prepare("SELECT id FROM otp_requests 
                WHERE student_number = ? AND otp = ? AND expires_at > NOW()");
            $verify_stmt->bind_param("ss", $student_number, $otp);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows > 0) {
                // OTP is correct - delete it
                $delete_stmt = $this->db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
                $delete_stmt->bind_param("s", $student_number);
                $delete_stmt->execute();
                
                return [
                    'success' => true,
                    'message' => 'OTP validated successfully',
                    'remaining_attempts' => 0
                ];
            } else {
                // Increment attempts
                $new_attempts = $current_attempts + 1;
                $remaining_attempts = 5 - $new_attempts;
                
                if ($new_attempts >= 5) {
                    // Delete if max attempts reached
                    $delete_stmt = $this->db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
                    $delete_stmt->bind_param("s", $student_number);
                    $delete_stmt->execute();
                    
                    return [
                        'success' => false,
                        'message' => 'Maximum attempts reached. Please request a new OTP.',
                        'remaining_attempts' => 0
                    ];
                }
                
                // Update attempts count
                $update_stmt = $this->db->prepare("UPDATE otp_requests SET attempts = ? WHERE student_number = ?");
                $update_stmt->bind_param("is", $new_attempts, $student_number);
                $update_stmt->execute();
                
                return [
                    'success' => false,
                    'message' => "Invalid OTP. {$remaining_attempts} attempts remaining.",
                    'remaining_attempts' => $remaining_attempts
                ];
            }
        } catch (Exception $e) {
            error_log("Error validating OTP: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while validating OTP.',
                'error' => true
            ];
        }
    }

    /**
     * Delete OTP after use or when generating a new one
     * 
     * @param string $student_number Student's ID number
     * @return bool Success of operation
     */
    private function deleteOTP($student_number) {
        $stmt = $this->db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
        $stmt->bind_param("s", $student_number);
        return $stmt->execute();
    }

    /**
     * Generate and store a new OTP for a student
     * 
     * @param string $student_number Student's ID number
     * @return string|bool Generated OTP or false on failure
     */
    public function createNewOTP($student_number) {
        $otp = $this->generateOTP();
        
        if ($this->storeOTP($student_number, $otp)) {
            return $otp;
        } else {
            return false;
        }
    }

    /**
     * Send OTP via email using PHPMailer
     * 
     * @param string $student_number Student's ID number
     * @param string $email Recipient email address
     * @param string $otp OTP to send
     * @param string $name Recipient's name (optional)
     * @return array Status and message
     */
    public function sendOTPEmail($student_number, $email, $otp, $name = '') {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Disable debug output in production
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Set timeout and keep alive
            $mail->Timeout = 60;
            $mail->SMTPKeepAlive = true;
            
            // Set character encoding
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom($_ENV['MAIL_USERNAME'], 'E-Halal System');
            $mail->addAddress($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Verification Code - E-Halal Voting System';
            
            // Build email body with responsive design
            $mailBody = $this->buildEmailTemplate($otp, $name, $student_number);
            $mail->Body = $mailBody;
            
            // Plain text alternative
            $mail->AltBody = "Your verification code is: $otp\nThis code will expire in {$this->otp_expiry_minutes} minutes.\nDo not share this code with anyone.";
            
            // Send the email
            $mail->send();
            
            return [
                'success' => true,
                'message' => "OTP sent successfully to $email"
            ];
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return [
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ];
        }

        // Close SMTP connection
        if ($mail->SMTPKeepAlive) {
            $mail->smtpClose();
        }
    }

    /**
     * Generate and store a new OTP for a student
     * 
     * @param string $student_number Student's ID number
     * @param string $name Recipient's name (optional)
     * @return array Status and message
     */
    public function generateAndSendOTP($student_number, $name = '') {
        try {
            // Delete any existing OTP for this student first
            $stmt = $this->db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
            $stmt->bind_param("s", $student_number);
            $stmt->execute();
            
            // Generate email from student number
            $email = $student_number . '@btech.ph.education';
            
        // Generate new OTP
        $otp = $this->createNewOTP($student_number);
        
        if ($otp) {
            // Send the OTP via email
            return $this->sendOTPEmail($student_number, $email, $otp, $name);
            }
            
            return [
                'success' => false,
                'message' => 'Failed to generate OTP'
            ];
        } catch (Exception $e) {
            error_log("Error in generateAndSendOTP: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing your request'
            ];
        }
    }
    
    /**
     * Set email configuration (optional method for runtime changes)
     */
    public function setEmailConfig($config) {
        $this->mail_config = array_merge($this->mail_config, $config);
    }
    
    /**
     * Create a professionally designed email template for OTP
     * 
     * @param string $otp The OTP code
     * @param string $name Recipient's name
     * @param string $student_number Student's ID number
     * @return string HTML email template
     */
    private function buildEmailTemplate($otp, $name, $student_number) {
        $greeting = empty($name) ? "Hello" : "Hello $name";
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>OTP Verification</title>
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
                    <h1>E-Halal Voting System</h1>
                </div>
                <div class="content">
                    <p>$greeting,</p>
                    <p>You have requested an OTP (One-Time Password) for accessing the E-Halal Voting System. Your verification code is:</p>
                    
                    <div class="otp-code">$otp</div>
                    
                    <div class="timer">This code will expire in {$this->otp_expiry_minutes} minutes</div>
                    
                    <p>If you did not request this code, please ignore this email or contact the administrator.</p>
                    
                    <p>Student Number: $student_number</p>
                    
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
    
    /**
     * Verify OTP and login the user
     * 
     * @param string $student_number Student's ID number
     * @param string $otp OTP to verify
     * @return array Status and user data
     */
    public function verifyOTPAndLogin($student_number, $otp) {
        // First verify the OTP
        $verification_result = $this->validateOTP($student_number, $otp);
        
        if ($verification_result['success']) {
            // OTP is valid, now login the user
            require_once __DIR__ . '/User.php';
            require_once __DIR__ . '/CustomSessionHandler.php';
            
            $user = new User();
            
            // Use the authenticateWithOTP method from the refactored User class
            if ($user->authenticateWithOTP($student_number)) {
                // Set the user session
                $user->setSession();
                
                // Get the voter data for the response
                $stmt = $this->db->prepare("SELECT * FROM voters WHERE student_number = ?");
                $stmt->bind_param("s", $student_number);
                $stmt->execute();
                $result = $stmt->get_result();
                $voter = $result->fetch_assoc();
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'voter' => $voter
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Student not found in the database'
                ];
            }
        }
        
        // If OTP verification failed, return the error
        return $verification_result;
    }
}
