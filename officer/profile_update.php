<?php
	include 'includes/session.php';

	if(isset($_GET['return'])){
		$return = $_GET['return'];
		
	}
	else{
		$return = 'home.php';
	}

	if(isset($_POST['save'])){
		$curr_password = $_POST['curr_password'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$photo = $_FILES['photo']['name'];

		// Prepare and execute a SELECT query to retrieve the user's data
		$sql = "SELECT * FROM admin WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $user['id']);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();

		// Verify the current password
		if(password_verify($curr_password, $user['password'])){
			// Check if a new photo is uploaded
			if(!empty($photo)){
				move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$photo);
				$filename = $photo;	
			}
			else{
				$filename = $user['photo'];
			}

			// Check if the new password matches the old one
			if($password == $user['password']){
				$password = $user['password'];
			}
			else{
				$password = password_hash($password, PASSWORD_DEFAULT);
			}

			// Prepare and execute the UPDATE query
			$sql = "UPDATE admin SET username = ?, password = ?, firstname = ?, lastname = ?, photo = ? WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("sssssi", $username, $password, $firstname, $lastname, $filename, $user['id']);
			if($stmt->execute()){
				$_SESSION['success'] = 'Admin profile updated successfully';
			}
			else{
				$_SESSION['error'] = $conn->error;
			}
			
		}
		else{
			$_SESSION['error'] = 'Incorrect password';
		}
	}
	else{
		$_SESSION['error'] = 'Fill up required details first';
	}

	header('location:'.$return);
?>
