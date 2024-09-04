<?php
    include 'includes/session.php';
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
    
        // Retrieve username and role from admin table
        $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
        $stmt = $conn->prepare($admin_sql);
        $stmt->bind_param("i", $_SESSION['admin']);
        $stmt->execute();
        $stmt->bind_result($username, $role);
        $stmt->fetch();
        $stmt->close();
    
        // Retrieve partylist name before update
        $old_partylist_info = get_partylist_info($conn, $id);
        $old_name = $old_partylist_info['name'];
    
        $sql = "UPDATE partylists SET name = ? WHERE id = ?";
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Partylist updated successfully';
            // Log the successful action
            log_action($conn, $username, $_SESSION['role'], "Updated Partylist: $old_name to $name");
        } else {
            $_SESSION['error'] = 'Failed to update partylist: ' . $stmt->error;
            // Log the error action
            log_action($conn, $username, $_SESSION['role'], "Error updating partylist: " . $stmt->error);
        }
    } else {
        $_SESSION['error'] = 'Fill up edit form first';
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