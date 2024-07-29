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

    // Retrieve candidate details before deletion
    $candidate_info = get_candidate_info($conn, $id);
    $candidate_firstname = $candidate_info['firstname'];
    $candidate_lastname = $candidate_info['lastname'];
    $candidate_position = $candidate_info['position'];
    $candidate_partylist = $candidate_info['partylist'];

    $sql = "DELETE FROM candidates WHERE id = ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Candidate deleted successfully';
        // Log the successful action
        log_action($conn, $username, $_SESSION['role'], "Deleted candidate: $candidate_firstname $candidate_lastname (Position: $candidate_position, Partylist: $candidate_partylist)");
    } else {
        $_SESSION['error'] = 'Failed to delete candidate: ' . $stmt->error;
        // Log the error action
        log_action($conn, $username, $_SESSION['role'], "Error deleting candidate: " . $stmt->error);
    }
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: candidates.php');

function get_candidate_info($conn, $id) {
    $sql = "SELECT candidates.firstname, candidates.lastname, positions.description AS position, partylists.name AS partylist
            FROM candidates
            INNER JOIN positions ON candidates.position_id = positions.id
            INNER JOIN partylists ON candidates.partylist_id = partylists.id
            WHERE candidates.id = ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate_info = $result->fetch_assoc();
    $stmt->close();
    
    return $candidate_info;
}

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}