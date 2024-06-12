<?php
include 'includes/session.php';

$sql = "DELETE FROM votes";
$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $_SESSION['success'] = "Votes reset successfully";
} else {
    $_SESSION['error'] = "Something went wrong in resetting";
}

$stmt->close();
header('location: votes.php');
