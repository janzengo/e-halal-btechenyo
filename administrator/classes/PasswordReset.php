<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class PasswordReset {
    private $db;
    private $mailer;
    private $token_length = 64;
    private $token_expiry_hours = 24;

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
        
        $this->mailer->Timeout = 60;
        $this->mailer->SMTPKeepAlive = true;
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Generate a secure random token
     */
    private function generateToken() {
        return bin2hex(random_bytes($this->token_length / 2));
    }

    /**
     * Create a password reset request and send email
     */
    public function createResetRequest($email, $name = '') {
        try {
            // Get admin gender
            $stmt = $this->db->prepare("SELECT gender FROM admin WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $adminData = $result->fetch_assoc();
            $gender = $adminData ? $adminData['gender'] : '';

            // Generate token
            $token = $this->generateToken();
            
            // Store reset request
            $stmt = $this->db->prepare(
                "INSERT INTO password_reset_requests (email, reset_token) VALUES (?, ?)"
            );
            
            $stmt->bind_param("ss", $email, $token);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create reset request');
            }

            // Send reset email
            return $this->sendResetEmail($email, $token, $name, $gender);

        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Rate limit exceeded') !== false) {
                return [
                    'success' => false,
                    'message' => 'Too many reset attempts. Please try again later.'
                ];
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send password reset email
     */
    private function sendResetEmail($email, $token, $name = '', $gender = '') {
        $this->mailer->clearAllRecipients();
        
        try {
            // Recipients
            $this->mailer->setFrom($_ENV['MAIL_USERNAME'], 'E-Halal BTECHenyo System');
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request - E-Halal BTECHenyo';
            
            $resetLink = BASE_URL . "administrator/auth/reset-password.php?token=" . $token;
            $title = $gender === 'Male' ? 'Mr.' : ($gender === 'Female' ? 'Ms.' : '');
            $greeting = empty($name) ? "Hello" : "Hello {$title} {$name}";
            
            $this->mailer->Body = $this->buildEmailTemplate($resetLink, $greeting);
            $this->mailer->AltBody = "Reset your password by clicking this link: $resetLink\nThis link will expire in {$this->token_expiry_hours} hours.\nIf you did not request this reset, please ignore this email.";
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => "Reset instructions sent to $email"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"
            ];
        }
    }

    /**
     * Validate reset token
     */
    public function validateToken($token) {
        try {
            $stmt = $this->db->prepare(
                "SELECT email, used FROM password_reset_requests 
                 WHERE reset_token = ? AND expires_at > NOW() AND used = 0"
            );
            
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ];
            }

            $data = $result->fetch_assoc();
            return [
                'success' => true,
                'email' => $data['email']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Reset password using token
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Start transaction
            $this->db->getConnection()->begin_transaction();

            // Validate token and get email
            $validation = $this->validateToken($token);
            if (!$validation['success']) {
                throw new Exception($validation['message']);
            }

            $email = $validation['email'];
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE admin SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update password');
            }

            // Mark token as used
            $stmt = $this->db->prepare(
                "UPDATE password_reset_requests SET used = 1 WHERE reset_token = ?"
            );
            $stmt->bind_param("s", $token);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update reset token status');
            }

            // Commit transaction
            $this->db->getConnection()->commit();
            
            return [
                'success' => true,
                'message' => 'Password has been reset successfully'
            ];

        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Build email template
     */
    private function buildEmailTemplate($resetLink, $greeting) {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Reset Request</title>
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
                .reset-button {
                    display: inline-block;
                    padding: 15px 30px;
                    background-color: #1d7c39;
                    color: white !important;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                    font-weight: bold;
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
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>E-Halal BTECHenyo Admin Portal</h1>
                </div>
                <div class="content">
                    <p>{$greeting},</p>
                    <p>We received a request to reset your password for your E-Halal BTECHenyo Admin Portal account.</p>
                    
                    <div class="button-container">
                        <a href="{$resetLink}" class="reset-button">Reset Password</a>
                    </div>
                    
                    <div class="timer">This link will expire in {$this->token_expiry_hours} hours</div>
                    
                    <p>If you did not request this password reset, please ignore this email or contact the system administrator immediately.</p>
                    
                    <p>For security reasons, this password reset link can only be used once.</p>
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