<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../init.php';
require_once '../classes/OTPMailer.php';
require_once '../classes/CustomSessionHandler.php';
require_once '../classes/User.php';
require_once '../classes/Database.php';

// Initialize classes
$db = Database::getInstance();
$session = CustomSessionHandler::getInstance();
$otpMailer = new OTPMailer();

// Test data
$student_number = isset($_GET['student']) ? $_GET['student'] : '';
$otp = isset($_GET['otp']) ? $_GET['otp'] : '';

if ($student_number && $otp) {
    try {
        // Test OTP verification
        echo "<h2>Testing OTP Verification</h2>";
        echo "<pre>";
        
        echo "Student Number: " . htmlspecialchars($student_number) . "\n";
        echo "OTP: " . htmlspecialchars($otp) . "\n\n";
        
        // Check OTP record
        $stmt = $db->prepare("SELECT * FROM otp_requests WHERE student_number = ?");
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $otpData = $result->fetch_assoc();
        
        echo "Current OTP Record:\n";
        print_r($otpData);
        echo "\n";
        
        // Test verification
        echo "Verification Result:\n";
        $result = $otpMailer->verifyOTPAndLogin($student_number, $otp);
        print_r($result);
        
        echo "</pre>";
    } catch (Exception $e) {
        echo "<h2>Error Occurred</h2>";
        echo "<pre>";
        echo "Error Message: " . $e->getMessage() . "\n\n";
        echo "Stack Trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
} else {
    echo "<h2>OTP Test Page</h2>";
    echo "<p>Use ?student=STUDENT_NUMBER&otp=OTP_CODE in the URL to test</p>";
} 