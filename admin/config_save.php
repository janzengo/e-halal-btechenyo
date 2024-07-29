<?php
include 'includes/session.php';

$return = 'home.php';
if (isset($_GET['return'])) {
    $return = $_GET['return'];
}

if (isset($_POST['save'])) {
    $title = $_POST['title'];

    // Retrieve username and role from admin table
    $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("i", $_SESSION['admin']);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();

    // Write to the config.ini file securely
    $file = 'config.ini';
    $content = 'election_title = ' . $title;

    // Check if the file is writable
    if (is_writable($file)) {
        // Write to the file using file locking to prevent race conditions
        if (file_put_contents($file, $content, LOCK_EX)) {
            $_SESSION['success'] = 'Election title updated successfully';
            // Log the successful action
            log_action($conn, $username, $_SESSION['role'], "Updated election title to: $title");
        } else {
            $_SESSION['error'] = 'Failed to update election title';
            // Log the error action
            log_action($conn, $username, $_SESSION['role'], "Error updating election title: failed to write to the config file");
        }
    } else {
        $_SESSION['error'] = 'The config file is not writable';
        // Log the error action
        log_action($conn, $username, $_SESSION['role'], "Error updating election title: config file is not writable");
    }
} else {
    $_SESSION['error'] = "Fill up config form first";
    // Log the error action
    log_action($conn, $username, $_SESSION['role'], "Attempted to update election title without filling up the form");
}

header('location: ' . $return);

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}