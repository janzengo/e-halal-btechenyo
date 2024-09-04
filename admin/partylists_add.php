<?php
include 'includes/session.php';

if (isset($_POST['add-partylist'])) {
    $name = $_POST['name'];

    // Retrieve username and role from admin table
    $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("i", $_SESSION['admin']);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();

    // Insert new partylist
    $sql = "INSERT INTO partylists (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Partylist added successfully';
        // Log the successful action
        log_action($conn, $username, $role, "Added new partylist: $name");
    } else {
        $_SESSION['error'] = 'Failed to add partylist: ' . $stmt->error;
        // Log the error action
        log_action($conn, $username, $role, "Error adding partylist: " . $stmt->error);
    }
    $stmt->close();
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

if (isset($_POST['origin']) && $_POST['origin'] == 'pre_election') {
    header('location: pre_election_candidates.php');
} else {
    header('location: partylists.php'); // Default redirect
}

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}