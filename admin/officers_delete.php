<?php
	include 'includes/session.php';

	if(isset($_POST['delete'])){
		$id = $_POST['id'];
		$sql = "DELETE FROM admin WHERE id = '$id'";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Officer deleted successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Select item to delete first';
	}

	if(isset($_POST['origin']) && $_POST['origin'] == 'pre_election'){
		header('location: pre_election.php');
	} else {
		header('location: officers.php'); // Default redirect
	}