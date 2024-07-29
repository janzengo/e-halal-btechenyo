<?php
include 'includes/session.php';

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}

// Retrieve username and role from admin table
$admin_sql = "SELECT username, role FROM admin WHERE id = ?";
$stmt = $conn->prepare($admin_sql);
$stmt->bind_param("i", $_SESSION['admin']);
$stmt->execute();
$stmt->bind_result($username, $role);
$stmt->fetch();
$stmt->close();

if (isset($_POST['bulk_delete']) && isset($_POST['ids'])) {
    $ids = $_POST['ids'];
    
    // Retrieve the student numbers for logging
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT voters_id, firstname, lastname FROM voters WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_numbers = [];
    $names = [];
    while ($row = $result->fetch_assoc()) {
        $student_numbers[] = $row['voters_id'];
        $names[] = $row['firstname'] . " " . $row['lastname'] . " (" . $row['voters_id'] . ")";
    }
    $stmt->close();

    // Delete the voters
    $sql = "DELETE FROM voters WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Voters deleted successfully';
        // Log the batch deletion action
        log_action($conn, $username, $role, "Deleted several voters: " . implode(', ', $names));
    } else {
        $_SESSION['error'] = $stmt->error;
        // Log the error
        log_action($conn, $username, $role, "Error deleting voters with student numbers: " . implode(', ', $student_numbers) . ". Error: " . $stmt->error);
    }

    $stmt->close();
} else if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Retrieve the student number for logging
    $sql = "SELECT voters_id, firstname, lastname FROM voters WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($student_number, $firstname, $lastname);
    $stmt->fetch();
    $stmt->close();

    // Delete the voter
    $sql = "DELETE FROM voters WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Voter deleted successfully';
        // Log the individual deletion action
        log_action($conn, $username, $role, "Deleted $firstname $lastname ($student_number) as voter");
        header('location: voters.php');
        exit;
    } else {
        $_SESSION['error'] = $stmt->error;
        // Log the error
        log_action($conn, $username, $role, "Error deleting voter with student number: $student_number. Error: " . $stmt->error);
        header('location: voters.php');
        exit;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Select item to delete first';
    // Log the attempt to delete without selecting an item
    log_action($conn, $username, $role, "Attempted to delete voters without selecting any item");
}
