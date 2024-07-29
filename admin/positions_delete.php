<?php
	include 'includes/session.php';

	if (isset($_POST['delete'])) {
		$id = $_POST['id'];
	
		// Retrieve username and role from admin table
		$admin_sql = "SELECT username, role FROM admin WHERE id = ?";
		$stmt = $conn->prepare($admin_sql);
		$stmt->bind_param("i", $_SESSION['admin']);
		$stmt->execute();
		$stmt->bind_result($username, $role);
		$stmt->fetch();
		$stmt->close();
	
		// Retrieve position description before deletion
		$position_info = get_position_info($conn, $id);
		$position_description = $position_info['description'];
	
		$sql = "DELETE FROM positions WHERE id = ?";
		
		// Prepare and execute the statement
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		if ($stmt->execute()) {
			$_SESSION['success'] = 'Position deleted successfully';
			// Log the successful action
			log_action($conn, $username, $_SESSION['role'], "Deleted position: $position_description");
		} else {
			$_SESSION['error'] = 'Failed to delete position: ' . $stmt->error;
			// Log the error action
			log_action($conn, $username, $_SESSION['role'], "Error deleting position: " . $stmt->error);
		}
	} else {
		$_SESSION['error'] = 'Select item to delete first';
	}
	
	header('location: positions.php');
	
	function get_position_info($conn, $id) {
		$sql = "SELECT description FROM positions WHERE id = ?";
		
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
	
	
