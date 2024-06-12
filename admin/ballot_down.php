<?php
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];

		$sql = "SELECT * FROM positions";
		$pstmt = $conn->prepare($sql);
		$pstmt->execute();
		$pquery = $pstmt->get_result();

		$output = array('error'=>false);

		$sql = "SELECT * FROM positions WHERE id=?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();

		$priority = $row['priority'] + 1;

		if($priority > $pquery->num_rows){
			$output['error'] = true;
			$output['message'] = 'This position is already at the bottom';
		}
		else{
			$sql = "UPDATE positions SET priority = priority - 1 WHERE priority = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $priority);
			$stmt->execute();

			$sql = "UPDATE positions SET priority = ? WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("ii", $priority, $id);
			$stmt->execute();
		}

		echo json_encode($output);

	}
