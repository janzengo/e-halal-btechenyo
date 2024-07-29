<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position = $_POST['position'];
    $party = $_POST['partylist'];
    $platform = $_POST['platform'];

    // Retrieve username and role from admin table
    $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("i", $_SESSION['admin']);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();

    // Retrieve candidate details before update
    $old_candidate_info = get_candidate_info($conn, $id);
    $old_firstname = $old_candidate_info['firstname'];
    $old_lastname = $old_candidate_info['lastname'];
    $old_position_name = $old_candidate_info['position_name'];
    $old_partylist_name = $old_candidate_info['partylist_name'];

    // Retrieve new position name
    $new_position_name = get_position_name($conn, $position);

    // Retrieve new partylist name
    $new_partylist_name = get_partylist_name($conn, $party);

    $sql = "UPDATE candidates SET firstname = ?, lastname = ?, position_id = ?, partylist_id = ?, platform = ? WHERE id = ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiis", $firstname, $lastname, $position, $party, $platform, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Candidate updated successfully';
        // Log the successful action
        log_action($conn, $username, $_SESSION['role'], "Updated Candidate: $old_firstname $old_lastname (Position: $old_position_name, Partylist: $old_partylist_name) to $firstname $lastname (Position: $new_position_name, Partylist: $new_partylist_name)");
    } else {
        $_SESSION['error'] = 'Failed to update candidate: ' . $stmt->error;
        // Log the error action
        log_action($conn, $username, $_SESSION['role'], "Error updating candidate: " . $stmt->error);
    }
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

header('location: candidates.php');

function get_candidate_info($conn, $id) {
    $sql = "SELECT candidates.firstname, candidates.lastname, positions.description AS position_name, partylists.name AS partylist_name
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

function get_position_name($conn, $position_id) {
    $sql = "SELECT description FROM positions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $stmt->bind_result($position_name);
    $stmt->fetch();
    $stmt->close();
    return $position_name;
}

function get_partylist_name($conn, $party_id) {
    $sql = "SELECT name FROM partylists WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $party_id);
    $stmt->execute();
    $stmt->bind_result($partylist_name);
    $stmt->fetch();
    $stmt->close();
    return $partylist_name;
}

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}