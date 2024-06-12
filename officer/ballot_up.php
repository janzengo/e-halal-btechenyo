<?php
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];

		$output = array('error'=>false);

		$sql = "SELECT * FROM positions WHERE id=?";	
		
		// Prepare the statement
		$stmt = $conn->prepare($sql);
		
		// Bind parameters and execute the statement
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();

			$priority = $row['priority'] - 1;

			if($priority == 0){
				$output['error'] = true;
				$output['message'] = 'This position is already at the top';
			}
			else{
				// Update priorities
				$sql1 = "UPDATE positions SET priority = priority + 1 WHERE priority = ?";
				$stmt1 = $conn->prepare($sql1);
				$stmt1->bind_param("i", $priority);
				$stmt1->execute();

				$sql2 = "UPDATE positions SET priority = ? WHERE id = ?";
				$stmt2 = $conn->prepare($sql2);
				$stmt2->bind_param("ii", $priority, $id);
				$stmt2->execute();
			}
		}
		else{
			$output['error'] = true;
			$output['message'] = 'Position not found';
		}

		echo json_encode($output);

	}
	