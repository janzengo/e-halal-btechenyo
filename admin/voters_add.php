<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$defaultProfilePicture = 'profile.jpg';
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$course = $_POST['course'];
		$voter = $_POST['studentNumber'];
		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		$filename = $defaultProfilePicture;
		$sql = "INSERT INTO voters (voters_id, course_id, password, firstname, lastname, photo) VALUES ('$voter', $course, '$password', '$firstname', '$lastname', '$filename')";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Voter added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}

	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: voters.php');
?>