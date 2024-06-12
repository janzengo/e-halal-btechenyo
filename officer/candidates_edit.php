<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position = $_POST['position'];
    $party = $_POST['partylist'];
    $platform = $_POST['platform'];

    $sql = "UPDATE candidates SET firstname = ?, lastname = ?, position_id = ?, partylist_id = ?, platform = ? WHERE id = ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiis", $firstname, $lastname, $position, $party, $platform, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Candidate updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update candidate: ' . $stmt->error;
    }
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

header('location: candidates.php');
