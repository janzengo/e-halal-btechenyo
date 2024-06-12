<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $password = $_POST['password'];
    $voter = $_POST['studentNumber'];
    $course = $_POST['course'];

    $sql = "SELECT * FROM voters WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($password == $row['password']) {
        $hashed_password = $row['password'];
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql = "UPDATE voters SET firstname = ?, lastname = ?, password = ?, course_id = ?, voters_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $lastname, $hashed_password, $course, $voter, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Voter updated successfully';
    } else {
        $_SESSION['error'] = $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

header('location: voters.php');