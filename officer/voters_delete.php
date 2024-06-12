<?php
include 'includes/session.php';

if (isset($_POST['ids'])) {
    $ids = $_POST['ids'];

    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "DELETE FROM voters WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Voters deleted successfully';
    } else {
        $_SESSION['error'] = $stmt->error;
    }

    $stmt->close();
} else if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    
    $sql = "DELETE FROM voters WHERE id = ?";
    $stmt = $conn->prepare($sql);

    
    $stmt->bind_param('i', $id);

    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Voter deleted successfully';
    } else {
        $_SESSION['error'] = $stmt->error;
    }


    $stmt->close();
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: voters.php');
