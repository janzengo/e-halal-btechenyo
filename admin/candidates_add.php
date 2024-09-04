<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position_id = $_POST['position'];
    $party_id = $_POST['partylist'];
    $platform = $_POST['platform'];
    $filename = $_FILES['photo']['name'];
    if (!empty($filename)) {
        move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
    }

    // Retrieve username and role from admin table
    $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("i", $_SESSION['admin']);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();

    // Retrieve position description
    $position_sql = "SELECT description FROM positions WHERE id = ?";
    $stmt = $conn->prepare($position_sql);
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $stmt->bind_result($position);
    $stmt->fetch();
    $stmt->close();

    // Retrieve partylist name
    $party_sql = "SELECT name FROM partylists WHERE id = ?";
    $stmt = $conn->prepare($party_sql);
    $stmt->bind_param("i", $party_id);
    $stmt->execute();
    $stmt->bind_result($partylist);
    $stmt->fetch();
    $stmt->close();

    // Use prepared statements for insertion
    $sql = "INSERT INTO candidates (position_id, partylist_id, firstname, lastname, photo, platform) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $position_id, $party_id, $firstname, $lastname, $filename, $platform);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Candidate added successfully';
        // Log the successful action
        log_action($conn, $username, $_SESSION['role'], "Added new candidate: $firstname $lastname for $position under $partylist partylist");
    } else {
        $_SESSION['error'] = 'Failed to add candidate: ' . $stmt->error;
        // Log the error action
        log_action($conn, $username, $_SESSION['role'], "Error adding candidate: " . $stmt->error);
    }
    $stmt->close();
} else {
    $_SESSION['error'] = 'Fill up add form first';
    // Log the error action
    log_action($conn, $username, $_SESSION['role'], "Attempted to add candidate without filling up the form");
}

if(isset($_POST['origin']) && $_POST['origin'] == 'pre_election'){
    header('location: pre_election_candidates.php');
} else {
    header('location: candidates.php'); // Default redirect
}

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}
