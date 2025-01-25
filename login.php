<?php
	require_once 'init.php';
	require_once 'classes/Database.php';
	require_once 'classes/CustomSessionHandler.php';
	require_once 'classes/User.php';

	$session = CustomSessionHandler::getInstance();
	$user = new User();

	if($user->isLoggedIn()) {
	    header('location: home.php');
	    exit();
	}

	if(isset($_POST['login'])) {
	    $voter_id = $_POST['voter'];
	    $password = $_POST['password'];
	    
	    if($user->login($voter_id, $password)) {
			$session->setSuccess('Vote wisely, BTECHenyo!');
	        header('location: home.php');
	        exit();
	    } else {
	        $session->setError('Invalid Student Number or password');
			header('location: index.php');
	    }
	}
?>