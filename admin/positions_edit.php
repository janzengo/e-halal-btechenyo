<?php
	include 'includes/session.php';

	if (isset($_POST['edit'])) {
		$id = $_POST['id'];
		$description = $_POST['description'];
		$max_vote = $_POST['max_vote'];
	
		// Retrieve username and role from admin table
		$admin_sql = "SELECT username, role FROM admin WHERE id = ?";
		$stmt = $conn->prepare($admin_sql);
		$stmt->bind_param("i", $_SESSION['admin']);
		$stmt->execute();
		$stmt->bind_result($username, $role);
		$stmt->fetch();
		$stmt->close();
	
		// Retrieve position description before update
		$old_position_info = get_position_info($conn, $id);
		$old_description = $old_position_info['description'];
		$old_max_vote = $old_position_info['max_vote'];
	
		$sql = "UPDATE positions SET description = ?, max_vote = ? WHERE id = ?";
		
		// Prepare and execute the statement
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sii", $description, $max_vote, $id);
		if ($stmt->execute()) {
			$_SESSION['success'] = 'Position updated successfully';
			// Log the successful action
			log_action($conn, $username, $_SESSION['role'], "Updated Position: $old_description (Max Vote: $old_max_vote) to $description (Max Vote: $max_vote)");
		} else {
			$_SESSION['error'] = 'Failed to update position: ' . $stmt->error;
			// Log the error action
			log_action($conn, $username, $_SESSION['role'], "Error updating position: " . $stmt->error);
		}
	} else {
		$_SESSION['error'] = 'Fill up edit form first';
	}
	
	header('location: positions.php');
	
	function get_position_info($conn, $id) {
		$sql = "SELECT description, max_vote FROM positions WHERE id = ?";
		
		// Prepare and execute the statement
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$position_info = $result->fetch_assoc();
		$stmt->close();
		
		return $position_info;
	}
	
	function log_action($conn, $username, $role, $details) {
		$sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sss", $username, $role, $details);
		$stmt->execute();
		$stmt->close();
	}
	