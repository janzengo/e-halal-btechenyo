<?php
include 'includes/session.php';

if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM candidates WHERE id = ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Candidate deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete candidate: ' . $stmt->error;
    }
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: candidates.php');
