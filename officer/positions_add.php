<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$description = $_POST['description'];
		$max_vote = $_POST['max_vote'];

		$sql = "SELECT priority FROM positions ORDER BY priority DESC LIMIT 1";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();

		$priority = $row['priority'] + 1;

		$sql = "INSERT INTO positions (description, max_vote, priority) VALUES (?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sii", $description, $max_vote, $priority);
		if($stmt->execute()){
			$_SESSION['success'] = 'Position added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: positions.php');
