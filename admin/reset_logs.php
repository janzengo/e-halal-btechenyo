<?php

include 'includes/session.php';

$sql = "TRUNCATE TABLE logs";
$stmt = $conn->prepare($sql);
if ($stmt->execute()) {
    $_SESSION['success'] = "Logs reset successfully";
} else {
    $_SESSION['error'] = "Something went wrong in resetting";
}

$stmt->close();
header('location: logs.php');