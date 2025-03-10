<?php
	require_once 'init.php';
	require_once 'classes/Database.php';
	require_once 'classes/CustomSessionHandler.php';
	require_once 'classes/User.php';
	require_once 'classes/OTPMailer.php';

	$session = CustomSessionHandler::getInstance();
	$user = new User();

	if($user->isLoggedIn()) {
	    header('location: home.php');
	    exit();
	}

	if(isset($_POST['login'])) {
	    $student_number = $_POST['voter'];
	    
	    // Redirect to OTP verification instead of direct login
	    if($user->authenticateWithOTP($student_number)) {
			// Redirect to OTP verification page
			header('location: otp_verify.php?student_number=' . urlencode($student_number));
	        exit();
	    } else {
	        $session->setError('Invalid Student Number or student not registered');
			header('location: index.php');
			exit();
	    }
	} else {
		header('location: index.php');
		exit();
	}
?>