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
    
        // Retrieve partylist name before deletion
        $partylist_info = get_partylist_info($conn, $id);
        $partylist_name = $partylist_info['name'];
    
        $sql = "DELETE FROM partylists WHERE id = ?";
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Partylist deleted successfully';
            // Log the successful action
            log_action($conn, $username, $_SESSION['role'], "Deleted partylist: $partylist_name");
        } else {
            $_SESSION['error'] = 'Failed to delete partylist: ' . $stmt->error;
            // Log the error action
            log_action($conn, $username, $_SESSION['role'], "Error deleting partylist: " . $stmt->error);
        }
    } else {
        $_SESSION['error'] = 'Select item to delete first';
    }
    
    header('location: partylists.php');
    
    function get_partylist_info($conn, $id) {
        $sql = "SELECT name FROM partylists WHERE id = ?";
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $partylist_info = $result->fetch_assoc();
        $stmt->close();
        
        return $partylist_info;
    }
    
    function log_action($conn, $username, $role, $details) {
        $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $role, $details);
        $stmt->execute();
        $stmt->close();
    }
    
    if(isset($_POST['origin']) && $_POST['origin'] == 'pre_election'){
        header('location: pre_election_candidates.php');
    } else {
        header('location: partylists.php'); // Default redirect
    }
