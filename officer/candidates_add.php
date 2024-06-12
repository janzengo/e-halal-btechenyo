<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$position = $_POST['position'];
		$party = $_POST['partylist'];
		$platform = $_POST['platform'];
		$filename = $_FILES['photo']['name'];
		if(!empty($filename)){
			move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);	
		}

		$sql = "INSERT INTO candidates (position_id, partylist_id, firstname, lastname, photo, platform) VALUES (?, ?, ?, ?, ?, ?)";
		
		// Prepare the statement
		$stmt = $conn->prepare($sql);
		
		// Bind parameters and execute the statement
		$stmt->bind_param("iissss", $position, $party, $firstname, $lastname, $filename, $platform);
		
		if($stmt->execute()){
			$_SESSION['success'] = 'Candidate added successfully';
		}
		else{
			$_SESSION['error'] = 'Failed to add candidate: ' . $stmt->error;
		}

	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: candidates.php');
