<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $defaultProfilePicture = 'profile.jpg';
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $course = $_POST['course'];
    $voters_id = $_POST['studentNumber'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $filename = $defaultProfilePicture;

    // Retrieve username and role from admin table
    $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("i", $_SESSION['admin']);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();

    // Check if voters_id already exists using prepared statement
    $check_sql = "SELECT * FROM voters WHERE voters_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $voters_id);
    $check_stmt->execute();
    $check_query = $check_stmt->get_result();

    if ($check_query->num_rows > 0) {
        $_SESSION['error'] = 'Voter ID already exists';
        // Log the error action
        log_action($conn, $username, $_SESSION['role'], "Attempted to add voter with existing ID: $voters_id");
    } else {
        // Use prepared statements for insertion
        $sql = "INSERT INTO voters (voters_id, course_id, password, firstname, lastname, photo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissss", $voters_id, $course, $password, $firstname, $lastname, $filename);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Voter added successfully';
            // Log the successful action
            log_action($conn, $username, $_SESSION['role'], "Added new voter: $firstname $lastname with Student Number: $voters_id");
        } else {
            $_SESSION['error'] = $stmt->error;
            // Log the error action
            log_action($conn, $username, $_SESSION['role'], "Error adding voter: $stmt->error");
        }
        $stmt->close();
    }
    $check_stmt->close();
} else {
    $_SESSION['error'] = 'Fill up add form first';
    // Log the error action
    log_action($conn, $username, $_SESSION['role'], "Attempted to add voter without filling up the form");
}

header('location: voters.php');

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}